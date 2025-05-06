<?php
/**
 * Configuration Example File
 * 
 * INSTRUCTIONS:
 * 1. Copy this file and rename it to 'config.php'
 * 2. Replace the placeholder API key with your actual OpenRouter API key
 * 3. Make sure config.php is in your .gitignore file to prevent accidental exposure
 */

return [
    'openrouter_api_key' => 'YOUR_OPENROUTER_API_KEY', // Replace with your actual OpenRouter API key
    
    // You can add additional configuration options below as your application grows
    'default_model' => 'openai/gpt-3.5-turbo',
    'temperature' => 0.7,
    'default_length' => 'medium',
];
