package main

import (
	"context"
	"encoding/json"
	"fmt"
	"io"
	"log"
	"net/http"
	"strings"

	"github.com/tmc/langchaingo/llms"
	"github.com/tmc/langchaingo/llms/openai"
)

func main() {
	// Initialize the OpenAI client
	llm, err := openai.New(openai.WithModel("gpt-4-turbo"))
	if err != nil {
		log.Fatal(err)
	}

	// Define the chat handler
	http.HandleFunc("/chat", func(w http.ResponseWriter, r *http.Request) {
		// Set headers for SSE
		w.Header().Set("Content-Type", "text/event-stream")
		w.Header().Set("Cache-Control", "no-cache")
		w.Header().Set("Connection", "keep-alive")
		w.Header().Set("Access-Control-Allow-Origin", "*")

		// Handle CORS preflight request
		if r.Method == http.MethodOptions {
			w.Header().Set("Access-Control-Allow-Methods", "POST, GET, OPTIONS")
			w.Header().Set("Access-Control-Allow-Headers", "Content-Type")
			w.WriteHeader(http.StatusNoContent)
			return
		}

		// Parse the request to get the message and history
		if r.Method != http.MethodPost {
			http.Error(w, "Only POST method is accepted", http.StatusMethodNotAllowed)
			return
		}

		// Read the request body
		body, err := io.ReadAll(r.Body)
		if err != nil {
			sendSSE(w, "error", fmt.Sprintf("Error reading request: %s", err.Error()))
			return
		}

		// Define a struct to hold the request body
		var chatRequest struct {
			Message string              `json:"message"`
			History []map[string]string `json:"history"`
		}

		// Decode the request body
		if err := json.Unmarshal(body, &chatRequest); err != nil {
			sendSSE(w, "error", fmt.Sprintf("Error parsing JSON: %s", err.Error()))
			return
		}

		// Create a context that cancels when the client disconnects
		ctx, cancel := context.WithCancel(r.Context())
		defer cancel()

		// Listen for client disconnection to cancel the context
		go func() {
			<-r.Context().Done()
			cancel() // This will cancel our context when the client disconnects
		}()

		// Build message history for the LLM
		var messages []llms.MessageContent
		for _, msg := range chatRequest.History {
			role := llms.ChatMessageTypeHuman
			if msg["role"] == "assistant" {
				role = llms.ChatMessageTypeAI
			}
			messages = append(messages, llms.TextParts(role, msg["content"]))
		}

		// Add the new user message
		messages = append(messages, llms.TextParts(llms.ChatMessageTypeHuman, chatRequest.Message))

		// Send an event to indicate the stream is starting
		sendSSE(w, "start", "Connection established")

		// Buffer to collect the full response
		var fullResponse strings.Builder

		// Generate content with streaming
		_, err = llm.GenerateContent(ctx,
			messages,
			llms.WithStreamingFunc(func(ctx context.Context, chunk []byte) error {
				// Check if the context has been canceled
				if ctx.Err() != nil {
					return ctx.Err() // This will stop the generation
				}

				// Append to the full response
				fullResponse.Write(chunk)

				// Send the FULL response so far with each update
				sendSSE(w, "message", fullResponse.String())
				return nil
			}),
		)

		if err != nil {
			// Check if the error is due to context cancellation
			if ctx.Err() != nil {
				// Send cancellation event
				sendSSE(w, "cancelled", "Stream was cancelled")
			} else {
				// Send other errors as an event
				sendSSE(w, "error", fmt.Sprintf("Error generating response: %s", err.Error()))
			}
			return
		}

		// Send the complete response for the client to save in history
		completeResponse := map[string]string{
			"role":    "assistant",
			"content": fullResponse.String(),
		}
		completeJSON, _ := json.Marshal(completeResponse)

		sendSSE(w, "complete", string(completeJSON))

		// Send an event to indicate the stream is done
		sendSSE(w, "done", "Stream complete")
	})

	// Define a simple homepage with the chat interface
	http.HandleFunc("/", func(w http.ResponseWriter, r *http.Request) {
		w.Header().Set("Content-Type", "text/html")
		w.Write([]byte(`
		<!DOCTYPE html>
		<html>
		<head>
			<title>Chat with GPT-4</title>
			<style>
				body {
					font-family: Arial, sans-serif;
					max-width: 800px;
					margin: 0 auto;
					padding: 20px;
				}
				.chat-container {
					display: flex;
					flex-direction: column;
					height: 70vh;
				}
				.chat-messages {
					flex: 1;
					overflow-y: auto;
					border: 1px solid #ddd;
					padding: 10px;
					margin-bottom: 10px;
					background-color: #f9f9f9;
				}
				.message {
					margin-bottom: 10px;
					padding: 8px 12px;
					border-radius: 5px;
					max-width: 80%;
					white-space: pre-wrap;
					word-wrap: break-word;
				}
				.user-message {
					background-color: #DCF8C6;
					margin-left: auto;
				}
				.assistant-message {
					background-color: #ECECEC;
				}
				.input-area {
					display: flex;
					margin-top: 10px;
				}
				.input-container {
					flex: 1;
					display: flex;
					flex-direction: column;
				}
				#user-input {
					width: 100%;
					height: 60px;
					padding: 10px;
					border: 1px solid #ddd;
					border-radius: 4px;
					resize: vertical;
					font-family: inherit;
					font-size: inherit;
				}
				button {
					padding: 10px 15px;
					background-color: #4CAF50;
					color: white;
					border: none;
					border-radius: 4px;
					cursor: pointer;
					margin-left: 10px;
					align-self: flex-end;
				}
				button:hover {
					background-color: #45a049;
				}
				.message-container {
					display: flex;
					flex-direction: column;
					margin-bottom: 15px;
					position: relative;
				}
				.message-container.user {
					align-items: flex-end;
				}
				.message-container.assistant {
					align-items: flex-start;
				}
				.message-actions {
					position: absolute;
					top: -8px;
					right: 0;
					display: none;
					gap: 5px;
				}
				.message-container.user:hover .message-actions {
					display: flex;
				}
				.action-button {
					padding: 2px 5px;
					background-color: #f0f0f0;
					color: #333;
					border: 1px solid #ccc;
					border-radius: 3px;
					cursor: pointer;
					font-size: 12px;
				}
				.action-button:hover {
					background-color: #e0e0e0;
				}
				.clear-button {
					margin-top: 10px;
					padding: 8px 12px;
					background-color: #f44336;
					color: white;
					border: none;
					border-radius: 4px;
					cursor: pointer;
				}
				.clear-button:hover {
					background-color: #d32f2f;
				}
				.stop-button {
					margin-top: 10px;
					padding: 8px 12px;
					background-color: #ff9800;
					color: white;
					border: none;
					border-radius: 4px;
					cursor: pointer;
					display: none;
				}
				.stop-button:hover {
					background-color: #f57c00;
				}
				.button-row {
					display: flex;
					gap: 10px;
					margin-top: 10px;
				}
				.edit-mode .message {
					display: none;
				}
				.edit-textarea {
					width: 100%;
					min-height: 60px;
					padding: 8px;
					border: 1px solid #4285f4;
					border-radius: 5px;
					font-family: inherit;
					font-size: inherit;
					resize: vertical;
					margin-bottom: 5px;
				}
				.edit-buttons {
					display: flex;
					gap: 5px;
					justify-content: flex-end;
				}
				.rewind-indicator {
					background-color: #ffeb3b;
					color: #333;
					padding: 5px 10px;
					text-align: center;
					margin: 10px 0;
					border-radius: 5px;
					font-size: 14px;
				}
				code {
					background-color: #f0f0f0;
					padding: 2px 4px;
					border-radius: 3px;
					font-family: monospace;
				}
				pre {
					background-color: #f0f0f0;
					padding: 10px;
					border-radius: 5px;
					overflow-x: auto;
					font-family: monospace;
				}
			</style>
		</head>
		<body>
			<h1>Chat with GPT-4</h1>
			<div class="chat-container">
				<div id="chat-messages" class="chat-messages"></div>
				<div class="input-area">
					<div class="input-container">
						<textarea id="user-input" placeholder="Type your message here..." autofocus></textarea>
					</div>
					<button id="send-button" onclick="sendMessage()">Send</button>
				</div>
				<div class="button-row">
					<button id="stop-button" class="stop-button" onclick="stopGeneration()">Stop Generation</button>
					<button class="clear-button" onclick="clearChat()">Clear Chat History</button>
				</div>
			</div>

			<script>
				// Store the conversation history
				let chatHistory = [];
				let currentEventSource = null;
				let currentController = null;

				// Check if we have history in localStorage
				const savedHistory = localStorage.getItem('chatHistory');
				if (savedHistory) {
					try {
						chatHistory = JSON.parse(savedHistory);
						// Display the saved conversation
						chatHistory.forEach(msg => {
							addMessageToUI(msg.content, msg.role === 'user');
						});
					} catch (e) {
						console.error('Error loading chat history:', e);
						localStorage.removeItem('chatHistory');
					}
				}

				// Function to add a message to the UI
				function addMessageToUI(content, isUser, messageId) {
					const messagesDiv = document.getElementById('chat-messages');

					const containerDiv = document.createElement('div');
					containerDiv.className = 'message-container ' + (isUser ? 'user' : 'assistant');

					// Assign a unique ID to each message container for easy reference
					const id = messageId || Date.now().toString();
					containerDiv.dataset.messageId = id;

					// Add message actions for user messages (edit & rewind)
					if (isUser) {
						const actionsDiv = document.createElement('div');
						actionsDiv.className = 'message-actions';

						const editButton = document.createElement('button');
						editButton.className = 'action-button';
						editButton.textContent = 'Edit';
						editButton.onclick = () => editMessage(id);

						const rewindButton = document.createElement('button');
						rewindButton.className = 'action-button';
						rewindButton.textContent = 'Rewind';
						rewindButton.onclick = () => rewindToMessage(id);

						actionsDiv.appendChild(editButton);
						actionsDiv.appendChild(rewindButton);
						containerDiv.appendChild(actionsDiv);
					}

					const messageDiv = document.createElement('div');
					messageDiv.className = 'message ' + (isUser ? 'user-message' : 'assistant-message');
					messageDiv.textContent = content;

					containerDiv.appendChild(messageDiv);
					messagesDiv.appendChild(containerDiv);
					messagesDiv.scrollTop = messagesDiv.scrollHeight;

					return { container: containerDiv, message: messageDiv, id: id };
				}

				// Function to update the assistant's message as it streams in
				function updateAssistantMessage(content) {
					const messagesDiv = document.getElementById('chat-messages');
					const containers = messagesDiv.getElementsByClassName('message-container assistant');

					// If there's an existing assistant message container, update it
					if (containers.length > 0) {
						const lastContainer = containers[containers.length - 1];
						const messageDiv = lastContainer.querySelector('.message');
						messageDiv.textContent = content;
					} else {
						// Otherwise create a new one
						addMessageToUI(content, false);
					}

					messagesDiv.scrollTop = messagesDiv.scrollHeight;
				}

				// Function to edit a message
				function editMessage(messageId) {
					const container = document.querySelector('.message-container[data-message-id="' + CSS.escape(messageId) + '"]');
					if (!container) return;

					// Add edit-mode class to the container
					container.classList.add('edit-mode');

					// Get the current message text
					const messageDiv = container.querySelector('.message');
					const currentText = messageDiv.textContent;

					// Create edit interface
					const editArea = document.createElement('textarea');
					editArea.className = 'edit-textarea';
					editArea.value = currentText;

					const buttonsDiv = document.createElement('div');
					buttonsDiv.className = 'edit-buttons';

					const saveButton = document.createElement('button');
					saveButton.className = 'action-button';
					saveButton.textContent = 'Save';
					saveButton.onclick = () => saveEdit(messageId, editArea.value);

					const cancelButton = document.createElement('button');
					cancelButton.className = 'action-button';
					cancelButton.textContent = 'Cancel';
					cancelButton.onclick = () => cancelEdit(messageId);

					buttonsDiv.appendChild(cancelButton);
					buttonsDiv.appendChild(saveButton);

					// Add edit interface to container
					container.appendChild(editArea);
					container.appendChild(buttonsDiv);

					// Focus the textarea
					editArea.focus();
				}

				// Function to save an edited message
				function saveEdit(messageId, newText) {
					if (!newText.trim()) return; // Don't save empty messages

					const container = document.querySelector('.message-container[data-message-id="' + CSS.escape(messageId) + '"]');
					if (!container) return;

					// Update the message text
					const messageDiv = container.querySelector('.message');
					messageDiv.textContent = newText;

					// Remove edit interface
					container.classList.remove('edit-mode');
					const editArea = container.querySelector('.edit-textarea');
					const buttonsDiv = container.querySelector('.edit-buttons');
					if (editArea) editArea.remove();
					if (buttonsDiv) buttonsDiv.remove();

					// Ask if user wants to rewind to this message
					const shouldRewind = confirm('Do you want to rewind the conversation to this message?');
					if (shouldRewind) {
						rewindToMessage(messageId);
					} else {
						// Update the chat history without rewinding
						const messageIndex = chatHistory.findIndex(msg => msg.id === messageId);
						if (messageIndex !== -1) {
							chatHistory[messageIndex].content = newText;
							saveHistory();
						}
					}
				}

				// Function to cancel editing a message
				function cancelEdit(messageId) {
					const container = document.querySelector('.message-container[data-message-id="' + CSS.escape(messageId) + '"]');
					if (!container) return;

					// Remove edit interface
					container.classList.remove('edit-mode');
					const editArea = container.querySelector('.edit-textarea');
					const buttonsDiv = container.querySelector('.edit-buttons');
					if (editArea) editArea.remove();
					if (buttonsDiv) buttonsDiv.remove();
				}

				// Function to rewind to a specific message
				function rewindToMessage(messageId) {
					// Find the message in the history
					const messageIndex = findMessageIndexById(messageId);
					if (messageIndex === -1) return;

					// Stop any ongoing generation
					stopGeneration();

					// Get the message container
					const container = document.querySelector('.message-container[data-message-id="' + CSS.escape(messageId) + '"]');
					const messageDiv = container.querySelector('.message');
					const updatedText = messageDiv.textContent;

					// Update the chat history to include this message with updated text
					const newHistory = chatHistory.slice(0, messageIndex);

					// Add the current message with updated text
					newHistory.push({
						role: 'user',
						content: updatedText,
						id: messageId
					});

					// Add a visual indicator for the rewind point
					const messagesDiv = document.getElementById('chat-messages');

					// Remove all messages after the rewind point
					const allContainers = Array.from(messagesDiv.querySelectorAll('.message-container'));
					const rewindIndex = allContainers.findIndex(c => c.dataset.messageId === messageId);

					// Add a rewind indicator
					const rewindIndicator = document.createElement('div');
					rewindIndicator.className = 'rewind-indicator';
					rewindIndicator.textContent = '↺ Conversation rewound to this point';

					// Remove messages after the rewind point
					allContainers.forEach((c, i) => {
						if (i > rewindIndex) {
							c.remove();
						}
					});

					// Insert the indicator after the message
					if (container.nextSibling) {
						messagesDiv.insertBefore(rewindIndicator, container.nextSibling);
					} else {
						messagesDiv.appendChild(rewindIndicator);
					}

					// Update chat history
					chatHistory = newHistory;
					saveHistory();

					// Auto-send the rewound message
					sendWithHistory(updatedText, newHistory.slice(0, -1));
				}

				// Find a message in chat history by ID
				function findMessageIndexById(messageId) {
					return chatHistory.findIndex(msg => msg.id === messageId);
				}

				// Function to send a message with specific history
				function sendWithHistory(message, history) {
					if (!message) return;

					// First, cancel any existing stream
					stopGeneration();

					// Create an empty assistant message for streaming updates
					addMessageToUI('', false);

					// Prepare the request
					const requestBody = {
						message: message,
						history: history
					};

					// Create an AbortController for this request
					currentController = new AbortController();

					// Show the stop button
					document.getElementById('stop-button').style.display = 'block';
					// Disable the send button while generating
					document.getElementById('send-button').disabled = true;

					// Make a POST request
					fetch('/chat', {
						method: 'POST',
						headers: {
							'Content-Type': 'application/json',
							'Accept': 'text/event-stream'
						},
						body: JSON.stringify(requestBody),
						signal: currentController.signal
					}).then(response => {
						const reader = response.body.getReader();
						const decoder = new TextDecoder();

						function readStream() {
							return reader.read().then(({done, value}) => {
								if (done) {
									// Stream is done
									document.getElementById('stop-button').style.display = 'none';
									document.getElementById('send-button').disabled = false;
									return;
								}

								const chunk = decoder.decode(value, {stream: true});
								processSSEChunk(chunk);
								return readStream();
							}).catch(error => {
								if (error.name === 'AbortError') {
									console.log('Request was aborted');
								} else {
									console.error('Error reading stream:', error);
									updateAssistantMessage('Error: ' + error.message);
								}
								document.getElementById('stop-button').style.display = 'none';
								document.getElementById('send-button').disabled = false;
							});
						}

						return readStream();
					}).catch(error => {
						if (error.name === 'AbortError') {
							console.log('Request was aborted');
						} else {
							console.error('Error:', error);
							updateAssistantMessage('Error: ' + error.message);
						}
						document.getElementById('stop-button').style.display = 'none';
						document.getElementById('send-button').disabled = false;
					});
				}

				// Function to save the history to localStorage
				function saveHistory() {
					localStorage.setItem('chatHistory', JSON.stringify(chatHistory));
				}

				// Function to clear chat history
				function clearChat() {
					chatHistory = [];
					localStorage.removeItem('chatHistory');
					document.getElementById('chat-messages').innerHTML = '';
				}

				// Function to stop the current generation
				function stopGeneration() {
					if (currentController) {
						currentController.abort();
						currentController = null;
					}

					if (currentEventSource) {
						currentEventSource.close();
						currentEventSource = null;
					}

					// Hide the stop button
					document.getElementById('stop-button').style.display = 'none';
					// Enable the send button
					document.getElementById('send-button').disabled = false;
				}

				// Function to send a message
				function sendMessage() {
					const userInput = document.getElementById('user-input');
					const message = userInput.value.trim();

					if (!message) return;

					// First, cancel any existing stream
					stopGeneration();

					// Add user message to UI and history
					addMessageToUI(message, true);
					chatHistory.push({role: 'user', content: message});
					saveHistory();

					// Clear input field
					userInput.value = '';

					// Create an empty assistant message for streaming updates
					addMessageToUI('', false);

					// Prepare the request
					const requestBody = {
						message: message,
						history: chatHistory.slice(0, -1) // Exclude the latest user message (we send it separately)
					};

					// Create an AbortController for this request
					currentController = new AbortController();

					// Show the stop button
					document.getElementById('stop-button').style.display = 'block';
					// Disable the send button while generating
					document.getElementById('send-button').disabled = true;

					// Use EventSource for SSE
					const eventSourceUrl = '/chat';

					// Make a POST request to setup the SSE connection
					fetch(eventSourceUrl, {
						method: 'POST',
						headers: {
							'Content-Type': 'application/json',
							'Accept': 'text/event-stream'
						},
						body: JSON.stringify(requestBody),
						signal: currentController.signal
					}).then(response => {
						const reader = response.body.getReader();
						const decoder = new TextDecoder();

						function readStream() {
							return reader.read().then(({done, value}) => {
								if (done) {
									// Stream is done
									document.getElementById('stop-button').style.display = 'none';
									document.getElementById('send-button').disabled = false;
									return;
								}

								const chunk = decoder.decode(value, {stream: true});
								processSSEChunk(chunk);
								return readStream();
							}).catch(error => {
								if (error.name === 'AbortError') {
									console.log('Request was aborted');
								} else {
									console.error('Error reading stream:', error);
									updateAssistantMessage('Error: ' + error.message);
								}
								document.getElementById('stop-button').style.display = 'none';
								document.getElementById('send-button').disabled = false;
							});
						}

						return readStream();
					}).catch(error => {
						if (error.name === 'AbortError') {
							console.log('Request was aborted');
						} else {
							console.error('Error:', error);
							updateAssistantMessage('Error: ' + error.message);
						}
						document.getElementById('stop-button').style.display = 'none';
						document.getElementById('send-button').disabled = false;
					});
				}

				// Process SSE chunks
				let buffer = '';
				function processSSEChunk(chunk) {
					buffer += chunk;

					// Process complete events
					while (true) {
						const eventEnd = buffer.indexOf('\n\n');
						if (eventEnd === -1) break;

						const eventText = buffer.substring(0, eventEnd);
						buffer = buffer.substring(eventEnd + 2);

						// Parse the event
						let eventType = null;
						let dataLines = [];

						const lines = eventText.split('\n');
						for (const line of lines) {
							if (line.startsWith('event:')) {
								eventType = line.substring('event:'.length).trim();
							} else if (line.startsWith('data:')) {
								dataLines.push(line.substring('data:'.length));
							}
						}

						if (eventType && dataLines.length) {
							// Join data lines with newlines
							const data = dataLines.join('\n');
							handleSSEEvent(eventType, data);
						}
					}
				}

				// Handle different SSE event types
				function handleSSEEvent(eventType, data) {
					switch(eventType) {
						case 'start':
							console.log('Stream started:', data);
							break;
						case 'message':
							// Update the message with the full content
							updateAssistantMessage(data);
							break;
						case 'complete':
							try {
								const completeMsg = JSON.parse(data);
								chatHistory.push(completeMsg);
								saveHistory();
							} catch (e) {
								console.error('Error parsing complete message:', e);
							}
							// Hide the stop button when complete
							document.getElementById('stop-button').style.display = 'none';
							// Enable the send button
							document.getElementById('send-button').disabled = false;
							break;
						case 'cancelled':
							console.log('Stream cancelled:', data);
							// We don't add cancelled responses to the history
							break;
						case 'error':
							console.error('Error from server:', data);
							updateAssistantMessage('Error: ' + data);
							// Hide the stop button on error
							document.getElementById('stop-button').style.display = 'none';
							// Enable the send button
							document.getElementById('send-button').disabled = false;
							break;
						case 'done':
							console.log('Stream complete:', data);
							// Clean up resources
							currentController = null;
							// Hide the stop button when done
							document.getElementById('stop-button').style.display = 'none';
							// Enable the send button
							document.getElementById('send-button').disabled = false;
							break;
					}
				}

				// Allow Enter key to send messages (Shift+Enter for new line)
				document.getElementById('user-input').addEventListener('keydown', function(e) {
					if (e.key === 'Enter' && !e.shiftKey) {
						e.preventDefault(); // Prevent default to avoid adding a newline
						sendMessage();
					}
				});
			</script>
		</body>
		</html>
		`))
	})

	// Start the server
	port := "8999"
	fmt.Printf("Server starting on port %s...\n", port)
	log.Fatal(http.ListenAndServe(":"+port, nil))
}

// Helper function to send an SSE event with proper formatting
func sendSSE(w http.ResponseWriter, eventType string, data string) {
	// Format: "event: TYPE\ndata: DATA\n\n"
	fmt.Fprintf(w, "event: %s\n", eventType)

	// Split data by newlines and send each line separately with its own "data:" prefix
	lines := strings.Split(data, "\n")
	for _, line := range lines {
		fmt.Fprintf(w, "data: %s\n", line)
	}

	// End the event with an empty line
	fmt.Fprint(w, "\n")

	// Flush the response writer to ensure data is sent immediately
	if flusher, ok := w.(http.Flusher); ok {
		flusher.Flush()
	}
}

var tools = []llms.Tool{} // Placeholder for future tool definitions
