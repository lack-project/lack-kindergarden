---
slugName: anthropic-client-creation
files:
- src/Driver/OpenAi/OpenAiChatSerializer.php
- src/Driver/OpenAi/OpenAiClient.php
- src/Driver/OpenAi/OpenAiRequest.php
editFiles:
- src/Driver/Anthropic/AnthropicChatSerializer.php
- src/Driver/Anthropic/AnthropicClient.php
- src/Driver/Anthropic/AnthropicRequest.php
original_prompt: erstelle einen Client für Anthropic nach dem vorbild von src/Driver/OpenAi/OpenAiChatSerializer.php
  src/Driver/OpenAi/OpenAiClient.php src/Driver/OpenAi/OpenAiRequest.php
---
## Files and Classes to Create

1. **src/Driver/Anthropic/AnthropicChatSerializer.php**
   - Class: `AnthropicChatSerializer`

2. **src/Driver/Anthropic/AnthropicClient.php**
   - Class: `AnthropicClient`

3. **src/Driver/Anthropic/AnthropicRequest.php**
   - Class: `AnthropicRequest`

## Files and Classes to Modify

No existing files need modification as we are creating new classes parallelto the existing OpenAI implementation.

## Implementation Details

### src/Driver/Anthropic/AnthropicChatSerializer.php

#### Objective
Create a serializer for Anthropic client similar to the OpenAI serializer, to manage the preparation and formatting of chat data specific to Anthropic's API requirements.

#### Changes
- **Class Definition**: `AnthropicChatSerializer` implements `ChatSerializer`.
- **Properties**: 
  - `model: string`
  - `temperature: float`
 
  - `maxTokens: int|null`
  - `stream: bool`

- **Methods**:
  - `private function _mapRole(ChatMessageRoleEnum $role): string`
    - Maps the `ChatMessageRoleEnum` to Anthropic specific roles.
  - `public function serialize(Chat $chat, ChatRequestDriver $chatRequestDriver): array`
    - Prepares and formats the data for the request to Anthropic's API using configured properties.
    - Handles specific formatting conditions unique to Anthropic for max tokens, temperature, and response format.

### src/Driver/Anthropic/AnthropicClient.php

#### Objective
Develop a client class for the Anthropic API, utilizing similar logic and structure to the existing OpenAI client to manage API requests and responses.

#### Changes
- **Class Definition**: `AnthropicClient`
- **Properties**:
  - `apiKey: string`
  - `defaultModel: string`
  - `requests: