<?php
require_once 'article_generator.php';
$article = '';
$topic = '';
$error = '';

/**
 * Sanitize input function to prevent SQL injection and XSS attacks
 *
 * @param string $input The user input to sanitize
 * @return string The sanitized input
 */
function sanitizeInput($input)
{
    // Trim whitespace
    $input = trim($input);

    // Remove HTML and PHP tags
    $input = strip_tags($input);

    // Convert special characters to HTML entities
    $input = htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');

    // Additional security measures:
    // Remove potentially dangerous characters
    $input = preg_replace('/[^\p{L}\p{N}\s\-\.,?!;:\'"\(\)]+/u', '', $input);

    return $input;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if it's an AJAX request for article generation
    if (isset($_POST['ajax_request']) && $_POST['ajax_request'] === 'generate') {
        // Sanitize the topic input
        $topic = isset($_POST['topic']) ? sanitizeInput($_POST['topic']) : '';
        
        // Validate the length option (only allow predefined values)
        $allowedLengths = ['short', 'medium', 'long'];
        $length = isset($_POST['length']) && in_array($_POST['length'], $allowedLengths)
            ? $_POST['length']
            : 'medium';
        
        $response = ['success' => false, 'article' => '', 'error' => ''];
        
        if (empty($topic)) {
            $response['error'] = "Please enter a topic";
        } else {
            try {
                $generator = new ArticleGenerator();
                $article = $generator->generateArticle($topic, $length);
                $response['success'] = true;
                $response['article'] = $article;
            } catch (Exception $e) {
                $response['error'] = "Error: " . htmlspecialchars($e->getMessage());
            }
        }
        
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;

    } else {
        // Regular form submission (non-AJAX)
        // Sanitize the topic input
        $topic = isset($_POST['topic']) ? sanitizeInput($_POST['topic']) : '';

        // Validate the length option (only allow predefined values)
        $allowedLengths = ['short', 'medium', 'long'];
        $length = isset($_POST['length']) && in_array($_POST['length'], $allowedLengths)
            ? $_POST['length']
            : 'medium';

        if (empty($topic)) {
            $error = "Please enter a topic";
        } else {
            try {
                $generator = new ArticleGenerator();
                $article = $generator->generateArticle($topic, $length);
            } catch (Exception $e) {
                $error = "Error: " . htmlspecialchars($e->getMessage());
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Article Generator</title>
    <link rel="stylesheet" href="style.css">
    <!-- Add CSP header to help prevent XSS attacks -->
    <meta http-equiv="Content-Security-Policy"
          content="default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline';">
    <style>
        .progress-indicator {
            display: none;
            margin-top: 15px;
            text-align: center;
            padding: 10px;
            background-color: #f0f8ff;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        
        .spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            margin-right: 10px;
            border: 3px solid rgba(0, 0, 0, 0.1);
            border-radius: 50%;
            border-top-color: #007bff;
            animation: spin 1s ease-in-out infinite;
            vertical-align: middle;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Article Generator</h1>
    <p>Generate high-quality articles using AI</p>

    <form id="article-form" method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" autocomplete="off">
        <!-- Add CSRF protection token (in a real app you'd implement this) -->
        <!-- <input type="hidden" name="csrf_token" value="<?php /* echo generateCSRFToken(); */ ?>"> -->
        <input type="hidden" name="ajax_request" value="generate" id="ajax-request">

        <div class="form-group">
            <label for="topic">Article Topic:</label>
            <input type="text" id="topic" name="topic"
                   value="<?php echo htmlspecialchars($topic); ?>"
                   required
                   pattern="[\p{L}\p{N}\s\-\.,?!;:'\(\)]{2,100}"
                   title="Please enter a valid topic (2-100 characters, no special characters)"
                   maxlength="100">
        </div>

        <div class="form-group">
            <label for="length">Article Length:</label>
            <select id="length" name="length">
                <option value="short" <?php echo ($length === 'short') ? 'selected' : ''; ?>>Short (~300 words)</option>
                <option value="medium" <?php echo ($length === 'medium') ? 'selected' : ''; ?>>Medium (~600 words)
                </option>
                <option value="long" <?php echo ($length === 'long') ? 'selected' : ''; ?>>Long (~1000 words)</option>
            </select>
        </div>

        <button type="submit" id="generate-button">Generate Article</button>
    </form>
    
    <div id="progress-indicator" class="progress-indicator">
        <div class="spinner"></div>
        <span>Generating your article... This may take a minute.</span>
    </div>

    <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div id="result" class="result" <?php echo !$article ? 'style="display:none"' : ''; ?>>
        <h2>Generated Article:</h2>
        <div id="article-content" class="article-content">
            <?php echo $article ? nl2br(htmlspecialchars($article)) : ''; ?>
        </div>
        <button onclick="copyToClipboard()">Copy to Clipboard</button>
    </div>
</div>

<script>
    function copyToClipboard() {
        const articleText = document.querySelector('.article-content').innerText;
        navigator.clipboard.writeText(articleText)
            .then(() => alert('Article copied to clipboard!'))
            .catch(err => console.error('Error copying text: ', err));
    }

    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('article-form');
        const generateButton = document.getElementById('generate-button');
        const progressIndicator = document.getElementById('progress-indicator');
        const resultDiv = document.getElementById('result');
        const articleContent = document.getElementById('article-content');

        form.addEventListener('submit', function (event) {
            event.preventDefault();
            
            const topicField = document.getElementById('topic');
            if (topicField.value.trim() === '') {
                alert('Please enter a topic');
                return;
            }
            
            // Show the progress indicator
            progressIndicator.style.display = 'block';
            generateButton.disabled = true;
            
            // Create FormData object from the form
            const formData = new FormData(form);
            
            // Send AJAX request using fetch
            fetch('<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                // Hide the progress indicator
                progressIndicator.style.display = 'none';
                generateButton.disabled = false;
                
                if (data.success) {
                    // Display the result
                    articleContent.innerHTML = data.article.replace(/\n/g, '<br>');
                    resultDiv.style.display = 'block';
                    // Scroll to the result
                    resultDiv.scrollIntoView({ behavior: 'smooth' });
                } else {
                    // Display error
                    alert(data.error || 'An error occurred while generating the article');
                }
            })
            .catch(error => {
                console.error('Error generating article:', error);
                progressIndicator.style.display = 'none';
                generateButton.disabled = false;
                alert('An error occurred. Please try again.');
            });
        });
    });
</script>
</body>
</html>
