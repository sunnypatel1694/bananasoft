<?php
session_start();  // Start the session to access session variables

// Include header and database connection
include("header1.php");
include("connection1.php");

// Initialize variables for results
$meaning = "";  
$error_message = ""; 
$chatgpt_response = ""; 
$abbreviations = [];  // This will store the abbreviations fetched from the database

// Handle form submission when the user clicks the 'Search by Database' button
if (isset($_POST['searchDatabase'])) {
    // Get the search input and convert it to uppercase
    $abbreviation = strtoupper(trim($_POST['search'])); // Convert the abbreviation to uppercase

    // Check if the search input is not empty
    if (empty($abbreviation)) {
        $error_message = "Please enter an abbreviation to search.";
    } else {
        // Query to search for the full form in the database
        $query = "SELECT abbreviation, meaning FROM abbreviations WHERE abbreviation LIKE ?";
        if ($stmt = $conn->prepare($query)) {
            // Bind parameters and execute the statement
            $search_query = "%" . $abbreviation . "%";  // Allow partial matching
            $stmt->bind_param("s", $search_query);
            $stmt->execute();
            $stmt->bind_result($short_form, $full_form);  // Fetch abbreviation and meaning
            
            // Store results in the array
            while ($stmt->fetch()) {
                // Convert full form to uppercase as well
                $full_form = strtoupper($full_form);
                $short_form = strtoupper($short_form);
                  // Ensure full form is uppercase
                $abbreviations[] = ['short_form' => $short_form, 'full_form' => $full_form];
            }
            $stmt->close();
        }

        // Check if no results were found
        if (empty($abbreviations)) {
            $error_message = "Abbreviation not found in the database.";
        }
    }
}

// Handle form submission for ChatGPT search
if (isset($_POST['searchChatGPT'])) {
    $abbreviation = strtoupper(trim($_POST['search'])); // Convert the abbreviation to uppercase

    // Check if the search input is empty
    if (empty($abbreviation)) {
        $error_message = "Please enter an abbreviation to search.";
    } else {
        // Call the ChatGPT API to search for the abbreviation's meaning
        $chatgpt_response = getChatGPTResponse($abbreviation);
    }
}

// Function to call ChatGPT API and get the response
function getChatGPTResponse($query) {
    $apiKey = 'hnRil3hTa9QKsUj7SPIEhTLCjeh4gJpLJkjvNOd1'; // Replace with actual API key
    $url = 'https://api.cohere.ai/v1/generate';

    $data = [
        "model" => "command-r-plus",
        "prompt" => "You are an AI assistant that provides accurate full forms of abbreviations. When a user enters an abbreviation, return its correct full form. If the abbreviation has multiple meanings, list them concisely.\n\nNow, provide the full form for: " . strtoupper($query),
        "max_tokens" => 50,
        "temperature" => 0.2,
        "stop_sequences" => ["\n"],
        "truncate" => "END"
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $response = curl_exec($ch);
    curl_close($ch);

    $response_data = json_decode($response, true);

    if (isset($response_data['generations'][0]['text'])) {
        return strtoupper($response_data['generations'][0]['text']);
    } else {
        return "Error: " . json_encode($response_data); // Debugging output
    }
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bootstrap Table with Search, Pagination, and Export</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <link rel="stylesheet" href="style1.css"> 
    <style>
        /* Ensures the body takes up the full height and uses Flexbox for layout */
body {
    background-image: url('11.jpg'); /* Set your image path here */
    background-size: cover;          /* Ensures the image covers the entire screen */
    background-position: center;     /* Centers the background image */
    background-attachment: fixed;    /* Keeps the image fixed during scrolling */
    height: 100vh;                   /* Ensures the body takes up the full height */
    margin: 0;
    display: flex;
    flex-direction: column;          /* Stacks children vertically */
}

/* Ensures the content fills the available space, pushing footer to the bottom */
.main-container {
    background-color: rgba(255, 255, 255, 0.8); /* Semi-transparent white background for readability */
    margin-top: 30px; /* Adds space from the top of the screen */
    text-align: center;     /* This makes the container take up available space */
}

/* Styling for the footer */
footer {
    text-align: center; /* Center footer text */
    background-color: #343a40; /* Dark background for footer */
    color: white; /* White text */
    padding: 7px; /* Some padding for better visibility */
    width: 100%;
    margin-top: auto; /* Push footer to the bottom */
}

h1 {
    font-size: 4rem; /* Makes the title larger */
    font-weight: bold; /* Makes the font bold */
    color: #333; /* Sets a darker color for the heading */
    margin-bottom: 20px;
    margin-top: 50px; /* Adds space below the heading */
}

#searchInput {
    text-transform: uppercase; /* Forces text to be uppercase */
    width: 100%;
    padding: 12px 20px;
    font-size: 16px;
    border-radius: 25px;
    border: 1px solid #ccc;
    outline: none;
}

#searchInput:focus {
    border-color: #007bff;
    box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
}


#submitButton {
    margin-top: 30px; /* Space above the button */
    padding: 10px 20px; /* Button padding */
    font-size: 16px; /* Font size for the button */
    background-color: #007bff; /* Blue background */
    color: white; /* White text */
    border: none; /* Remove the border */
    border-radius: 25px; /* Rounded corners */
    cursor: pointer; /* Changes cursor to pointer on hover */
    width: 30%; /* Adjust width of the button */
    max-width: 300px; /* Max width to avoid stretching */
}

.error-message {
    color: red;
    font-weight: bold;
    margin-top: 20px;
}

table {
            width: 100%;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px;
            text-align: center;
        }

        th {
            background-color: #007bff;
            color: white;
        }

        td {
            background-color: #f2f2f2;
        }
        
        .loading-indicator {
            color: #007bff;
            font-size: 18px;
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="main-container">
    <div class="col-6 container mt-3">
        <h1>Abbreviation Search</h1>

        <!-- Display error message if any -->
        <?php if ($error_message != ""): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <input type="text" id="searchInput" name="search" class="form-control mb-3" placeholder="Search Google or type a URL" required>

            <button type="submit" name="searchDatabase" id="submitButton" class="btn btn-primary">Search by Database</button>
            <button type="submit" name="searchChatGPT" id="submitButton" class="btn btn-secondary">Search by Gemini AI</button>
        </form>

        <!-- Display results from Database -->
        <?php if (!empty($abbreviations)): ?>
            <div class="result">
                <h5>Search Results from Database:</h5>
                <table>
                    <thead>
                        <tr>
                            <th>Abbreviation</th>
                            <th>Full Form</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($abbreviations as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['short_form']); ?></td>
                                <td><?php echo htmlspecialchars($row['full_form']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <!-- Display result from ChatGPT -->
        <?php if (!empty($chatgpt_response)): ?>
            <div class="result">
                <h5>Full Form from Gemini AI:</h5>
                <table>
                    <thead>
                        <tr>
                            <th>Abbreviation</th>
                            <th>Full Form</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?php echo htmlspecialchars($abbreviation); ?></td>
                            <td><?php echo htmlspecialchars($chatgpt_response); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <!-- Display loading indicator -->
        <?php if (empty($chatgpt_response) && isset($_POST['searchChatGPT'])): ?>
            <div class="loading-indicator">Loading response from Gemini AI...</div>
        <?php endif; ?>
    </div>
</div>

<?php include('footer1.php'); ?>
</body>
</html>
