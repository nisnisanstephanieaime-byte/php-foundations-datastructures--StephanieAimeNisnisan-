<?php
class Node {
    public $data;
    public $left;
    public $right;

    public function __construct($data) {
        $this->data = $data;
        $this->left = null;
        $this->right = null;
    }
}

$jsonPath = __DIR__ . "/books.json";
if (!file_exists($jsonPath)) {
    die("Error: books.json file not found at $jsonPath");
}

$books = json_decode(file_get_contents($jsonPath), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    die("Error decoding JSON: " . json_last_error_msg());
}

$hashTable = [];
foreach ($books as $book) {
    $key = md5(strtolower($book['title'] . ' ' . $book['author']));
    $hashTable[$key] = new Node($book);
}

function searchBook($query, $hashTable) {
    $query = strtolower($query);
    $results = [];
    foreach ($hashTable as $node) {
        $book = $node->data;
        if (
            strpos(strtolower($book['title']), $query) !== false ||
            strpos(strtolower($book['author']), $query) !== false
        ) {
            $results[] = $book;
        }
    }
    return $results;
}

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$results = [];
if (!empty($search)) {
    $results = searchBook($search, $hashTable);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Library Hashtable Search</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    * {
        box-sizing: border-box;
        font-family: "Poppins", sans-serif;
    }

   body {
    margin: 0;
    padding: 40px;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    align-items: center;
    background: linear-gradient(180deg, #111 0%, #1b1b1b 100%);
    color: #e0e0e0;
}

h1 {
    font-size: 2.5rem;
    letter-spacing: 1px;
    text-align: center;
    background: linear-gradient(90deg, #7FDBFF, #f6f6f6ff);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    margin-bottom: 30px;
    text-shadow: 0 0 10px rgba(127, 219, 255, 0.3);
}

form {
    background: #1e1e1e;
    padding: 25px 30px;
    border-radius: 15px;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.4);
    display: flex;
    gap: 12px;
    width: 100%;
    max-width: 550px;
    border: 1px solid #2c2c2c;
}

input[type="text"] {
    flex: 1;
    padding: 12px 15px;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    background: #2a2a2a;
    color: #f0f0f0;
    outline: none;
    transition: 0.3s;
}

input[type="text"]:focus {
    background: #333;
    box-shadow: 0 0 8px rgba(127, 219, 255, 0.5);
}

button {
    background: linear-gradient(135deg, #839fe6ff, #304df4ff);
    color: white;
    border: none;
    padding: 12px 20px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    font-size: 15px;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
}

button:hover {
    transform: translateY(-2px) scale(1.03);
    box-shadow: 0 0 10px rgba(127, 219, 255, 0.6);
}

.results {
    margin-top: 35px;
    width: 100%;
    max-width: 700px;
}

.book {
    background: #1b1b1b;
    border: 1px solid #2a2a2a;
    padding: 18px 20px;
    border-radius: 12px;
    margin-bottom: 18px;
    box-shadow: 0 3px 12px rgba(0, 0, 0, 0.3);
    display: flex;
    gap: 18px;
    align-items: flex-start;
    transition: 0.25s ease;
}

.book:hover {
    background: #222;
    transform: translateY(-2px);
    box-shadow: 0 0 10px rgba(127, 219, 255, 0.2);
}

.book img {
    width: 90px;
    height: auto;
    border-radius: 8px;
    object-fit: cover;
    box-shadow: 0 0 6px rgba(0,0,0,0.5);
}

.book strong {
    color: #7FDBFF;
}

.book a {
    color: #B10DC9;
    text-decoration: none;
    font-weight: 600;
}

.book a:hover {
    text-decoration: underline;
}

footer {
    margin-top: auto;
    padding: 20px;
    text-align: center;
    color: #888;
    font-size: 14px;
}


    }
</style>
</head>
<body>
    <h1>🔮 Library Hashtable Search</h1>

    <form method="get">
        <input type="text" name="search" placeholder="Type a title or author..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit"><i class="fa fa-search"></i> Search</button>
    </form>

    <div class="results">
        <?php if (!empty($search)): ?>
            <h3>Results for "<?= htmlspecialchars($search) ?>"</h3>
            <?php if (empty($results)): ?>
                <p class="no-result">No matching books found.</p>
            <?php else: ?>
                <?php foreach ($results as $book): ?>
                    <div class="book">
                        <?php if (!empty($book['imageLink'])): ?>
                            <img src="<?= htmlspecialchars($book['imageLink']) ?>" alt="Book cover">
                        <?php endif; ?>
                        <div>
                            <strong>Title:</strong> <?= htmlspecialchars($book['title']) ?><br>
                            <strong>Author:</strong> <?= htmlspecialchars($book['author']) ?><br>
                            <?php if (!empty($book['category'])): ?>
                                <strong>Category:</strong> <?= htmlspecialchars($book['category']) ?><br>
                            <?php endif; ?>
                            <strong>Language:</strong> <?= htmlspecialchars($book['language']) ?><br>
                            <strong>Year:</strong> <?= htmlspecialchars($book['year']) ?><br>
                            <?php if (!empty($book['link'])): ?>
                                <a href="<?= htmlspecialchars($book['link']) ?>" target="_blank">More info →</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    
</body>
</html>
