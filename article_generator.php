<?php

class ArticleGenerator
{
    private $apiKey;
    private $apiUrl = 'https://openrouter.ai/api/v1/chat/completions';
    private $config;

    public function __construct()
    {
        $this->config = require_once 'config.php';
        $this->apiKey = $this->config['openrouter_api_key'];

        if (empty($this->apiKey)) {
            throw new Exception("OpenRouter API key is not configured");
        }
    }

    public function generateArticle($topic, $length = null)
    {
        // Use provided length or default from config
        $length = $length ?? $this->config['default_length'] ?? 'medium';

        // Define word count based on selected length
        $wordCount = match ($length) {
            'short' => 300,
            'long' => 1000,
            default => 600, // medium
        };

        // Prepare system message with instructions
        $systemMessage = "You are a professional content writer. Create a well-structured, informative article about '{$topic}'. 
        The article should be approximately {$wordCount} words and include an engaging title, introduction, 
        several body paragraphs with subheadings, and a conclusion. 
        Use a professional, informative tone and ensure the content is original and engaging.";

        // Prepare the messages array
        $messages = [
            [
                'role' => 'system',
                'content' => $systemMessage
            ],
            [
                'role' => 'user',
                'content' => "Write an article about: {$topic}"
            ]
        ];

        // Get model from config or use default
        $model = $this->config['default_model'] ?? 'openai/gpt-3.5-turbo';

        // Get temperature from config or use default
        $temperature = $this->config['temperature'] ?? 0.7;

        // Prepare the API request data
        $data = [
            'model' => $model,
            'messages' => $messages,
            'temperature' => $temperature,
            'max_tokens' => $wordCount * 3, // Approximate tokens for desired word count
        ];

        // Call the API
        return $this->callOpenRouterAPI($data);
    }

    private function callOpenRouterAPI($data)
    {
        // Set up cURL request
        $ch = curl_init($this->apiUrl);

        // Prepare headers
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey,
            'HTTP-Referer: http://localhost', // Update with your site URL in production
            'X-Title: Article Generator'
        ];

        // Set cURL options
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // Execute the request
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // Check for errors
        if (curl_errno($ch)) {
            curl_close($ch);
            throw new Exception("API Request failed: " . curl_error($ch));
        }

        curl_close($ch);

        // Process the response
        if ($httpCode !== 200) {
            $errorData = json_decode($response, true);
            $errorMessage = $errorData['error']['message'] ?? "API returned error code: {$httpCode}";
            throw new Exception($errorMessage);
        }

        $responseData = json_decode($response, true);

        // Extract the generated text
        if (isset($responseData['choices'][0]['message']['content'])) {
            return $responseData['choices'][0]['message']['content'];
        } else {
            throw new Exception("Unexpected API response format");
        }
    }
}
