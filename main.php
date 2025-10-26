<?php
function loadBooks() {
    $path = __DIR__ . '/books.json';
    if (!file_exists($path)) return [];
    $json = file_get_contents($path);
    return json_decode($json, true);
}

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

class BST {
    public $root;
    public function __construct() { $this->root = null; }
    public function insert($data) { $this->root = $this->insertRec($this->root, $data); }
    private function insertRec($root, $data) {
        if ($root === null) return new Node($data);
        if (strcasecmp($data['title'], $root->data['title']) < 0)
            $root->left = $this->insertRec($root->left, $data);
        else $root->right = $this->insertRec($root->right, $data);
        return $root;
    }
    public function inorder($root, &$res) {
        if ($root !== null) {
            $this->inorder($root->left, $res);
            $res[] = $root->data;
            $this->inorder($root->right, $res);
        }
    }
}

class HashTable {
    private $table;
    public function __construct() { $this->table = []; }
    private function hash($key) { return crc32(strtolower($key)) % 50; }
    public function insert($key, $value) {
        $index = $this->hash($key);
        $this->table[$index][] = $value;
    }
    public function search($key) {
        $index = $this->hash($key);
        if (isset($this->table[$index])) {
            foreach ($this->table[$index] as $item) {
                if (strcasecmp($item['title'], $key) == 0) return $item;
            }
        }
        return null;
    }
}

function countBooksRecursively($books) {
    if (empty($books)) return 0;
    array_pop($books);
    return 1 + countBooksRecursively($books);
}

$books = loadBooks();
$bst = new BST();
$hash = new HashTable();
foreach ($books as $book) {
    $bst->insert($book);
    $hash->insert($book['title'], $book);
}

$resultBooks = [];
if (isset($_GET['view'])) {
    $view = $_GET['view'];
    if ($view == 'bst') $bst->inorder($bst->root, $resultBooks);
    elseif ($view == 'hash' && isset($_GET['search'])) {
        $searchKey = $_GET['search'];
        $b = $hash->search($searchKey);
        if ($b) $resultBooks[] = $b;
    } elseif ($view == 'recursive') {
        $total = countBooksRecursively($books);
        $resultBooks = $books;
    } else $resultBooks = $books;
} else $resultBooks = $books;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Library Mini System v7</title>
<style>
body {
    background: #0a0a0f;
    color: #e0e0e0;
    font-family: 'Poppins', sans-serif;
    margin: 0;
    padding: 0;
}
header {
    background: linear-gradient(90deg, #00e0ff, #007bff);
    padding: 18px;
    text-align: center;
    color: black;
    font-weight: bold;
    font-size: 1.6em;
    letter-spacing: 3px;
    text-transform: uppercase;
}
.container { width: 90%; margin: 30px auto; }
.nav { display: flex; justify-content: center; gap: 15px; margin-bottom: 25px; }
.nav a {
    text-decoration: none;
    background: #0af;
    color: #fff;
    padding: 10px 20px;
    border-radius: 10px;
    font-weight: bold;
    transition: 0.3s;
}
.nav a:hover { background: #0ff; color: black; transform: scale(1.05); }

.search-box { text-align: center; margin-bottom: 30px; }
.search-box input {
    padding: 10px;
    width: 40%;
    border-radius: 8px;
    border: none;
    outline: none;
    font-size: 1em;
}
.search-box button {
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    margin-left: 10px;
    background: #0ff;
    color: black;
    font-weight: bold;
    cursor: pointer;
}

.books {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 25px;
}

.book {
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 0 15px #0ff3;
    transition: 0.3s;
    color: white;
    background: #111;
    cursor: pointer;
}
.book:hover { transform: scale(1.05); box-shadow: 0 0 25px #0ff; }
.book img {
    width: 100%;
    height: 280px;
    object-fit: cover;
}
.book-content {
    padding: 10px;
}
.book h3 { color: #0ff; margin: 8px 0; font-size: 1.1em; }
.book p { margin: 3px 0; font-size: 0.9em; }

footer {
    text-align: center;
    padding: 20px;
    color: #666;
    margin-top: 40px;
    border-top: 1px solid #222;
}
.modal-bg {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.8);
    justify-content: center;
    align-items: center;
    z-index: 50;
}
.modal {
    background: #111;
    border: 1px solid #0ff3;
    padding: 25px;
    border-radius: 12px;
    width: 90%;
    max-width: 500px;
    color: #fff;
    box-shadow: 0 0 25px #0ff3;
    position: relative;
}
.modal h2 { margin-top: 0; color: #0ff; }
.modal button.close {
    position: absolute;
    top: 10px;
    right: 10px;
    background: none;
    border: none;
    color: #0ff;
    font-size: 1.3em;
    cursor: pointer;
}
.modal a {
    display: inline-block;
    margin-top: 15px;
    padding: 10px 20px;
    background: linear-gradient(90deg, #00e0ff, #007bff);
    color: black;
    font-weight: bold;
    text-decoration: none;
    border-radius: 8px;
}
</style>
</head>
<body>
<header>Library Mini System</header>
<div class="container">
    <div class="nav">
        <a href="?view=all">All Books</a>
        <a href="?view=bst">BST View</a>
        <a href="?view=recursive">Recursive View</a>
    </div>
    <div class="search-box">
        <form method="get">
            <input type="text" name="search" placeholder="Search by title...">
            <input type="hidden" name="view" value="hash">
            <button type="submit">Search</button>
        </form>
    </div>

    <div class="books">
        <?php if (!empty($resultBooks)): ?>
            <?php foreach ($resultBooks as $book): 
                $img = !empty($book['imageLink'])
                    ? $book['imageLink']
                    : 'https://upload.wikimedia.org/wikipedia/commons/thumb/a/ac/No_image_available.svg/480px-No_image_available.svg.png';
                $desc = htmlspecialchars($book['description'] ?? 'No description available');
                $link = htmlspecialchars($book['link'] ?? '#');
            ?>
                <div class="book" 
                     data-title="<?= htmlspecialchars($book['title']) ?>" 
                     data-author="<?= htmlspecialchars($book['author'] ?? 'Unknown') ?>"
                     data-genre="<?= htmlspecialchars($book['genre'] ?? 'N/A') ?>"
                     data-year="<?= htmlspecialchars($book['year'] ?? 'N/A') ?>"
                     data-desc="<?= $desc ?>"
                     data-link="<?= $link ?>">
                    <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($book['title']) ?>">
                    <div class="book-content">
                        <h3><?= htmlspecialchars($book['title']) ?></h3>
                        <p><strong>Author:</strong> <?= htmlspecialchars($book['author'] ?? 'Unknown') ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align:center;">No books found.</p>
        <?php endif; ?>
    </div>
</div>

<div class="modal-bg" id="modal">
    <div class="modal">
        <button class="close" onclick="closeModal()">×</button>
        <h2 id="mTitle"></h2>
        <p><strong>Author:</strong> <span id="mAuthor"></span></p>
        <p><strong>Genre:</strong> <span id="mGenre"></span></p>
        <p><strong>Year:</strong> <span id="mYear"></span></p>
        <p id="mDesc"></p>
        <a id="mLink" href="#" target="_blank">Read Book</a>
    </div>
</div>

<footer>Dark Neon Library System © 2025</footer>

<script>
const books = document.querySelectorAll('.book');
const modal = document.getElementById('modal');
const mTitle = document.getElementById('mTitle');
const mAuthor = document.getElementById('mAuthor');
const mGenre = document.getElementById('mGenre');
const mYear = document.getElementById('mYear');
const mDesc = document.getElementById('mDesc');
const mLink = document.getElementById('mLink');

books.forEach(b => {
    b.addEventListener('click', () => {
        mTitle.textContent = b.dataset.title;
        mAuthor.textContent = b.dataset.author;
        mGenre.textContent = b.dataset.genre;
        mYear.textContent = b.dataset.year;
        mDesc.textContent = b.dataset.desc;
        mLink.href = b.dataset.link;
        modal.style.display = 'flex';
    });
});
function closeModal() { modal.style.display = 'none'; }
modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });
</script>
</body>
</html>
