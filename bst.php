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

$booksFile = __DIR__ . '/books.json';
if (!file_exists($booksFile)) die("❌ Error: books.json not found.");
$booksData = json_decode(file_get_contents($booksFile), true);
if (!is_array($booksData)) die("❌ Error: Invalid books.json format.");

$nodes = array_map(fn($b) => new Node($b), $booksData);
usort($booksData, fn($a, $b) => strcmp($a['title'], $b['title']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>📚 Book Explorer</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap');
:root{--blue-bg:#0e7784;--light-modal:#dfe5c7;--text-dark:#0f172a;--shadow:rgba(0,0,0,0.15);}
body{font-family:'Poppins',sans-serif;background-color:var(--blue-bg);color:var(--text-dark);margin:0;padding:0;min-height:100vh;}
h1{text-align:center;color:white;margin:40px 0 10px;font-size:2.2em;}
.search-container{text-align:center;margin-bottom:40px;}
#searchInput{width:60%;padding:12px 18px;font-size:16px;border-radius:50px;border:2px solid var(--light-modal);outline:none;transition:all 0.3s ease;background:white;}
#searchInput:focus{box-shadow:0 4px 15px rgba(255,255,255,0.3);transform:scale(1.03);}
.book-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(230px,1fr));gap:25px;padding:20px 60px 60px;}
.card{background:white;border-radius:16px;box-shadow:0 6px 14px var(--shadow);text-align:center;padding:18px;cursor:pointer;transition:all 0.3s ease;border:3px solid transparent;}
.card:hover{transform:translateY(-8px);border-color:var(--light-modal);box-shadow:0 10px 20px rgba(223,229,199,0.5);}
.card img{width:100%;height:240px;object-fit:cover;border-radius:10px;}
.card strong{display:block;font-size:1.1em;margin-top:10px;color:var(--blue-bg);}
.small{color:#475569;font-size:0.9em;margin-top:3px;}
.modal{display:none;position:fixed;z-index:999;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.6);justify-content:center;align-items:center;animation:fadeIn 0.3s ease;}
@keyframes fadeIn{from{opacity:0;}to{opacity:1;}}
.modal-content{background:var(--light-modal);border-radius:18px;padding:25px;width:90%;max-width:430px;text-align:center;position:relative;box-shadow:0 8px 25px rgba(0,0,0,0.25);border-top:6px solid var(--blue-bg);animation:slideUp 0.4s ease;}
@keyframes slideUp{from{transform:translateY(30px);opacity:0;}to{transform:translateY(0);opacity:1;}}
.modal-content img{width:100%;height:260px;object-fit:cover;border-radius:12px;margin-bottom:15px;}
.close-btn{position:absolute;top:12px;right:15px;background:var(--blue-bg);border:none;color:white;font-size:18px;padding:6px 12px;border-radius:50%;cursor:pointer;transition:background 0.2s;}
.close-btn:hover{background:#095d67;}
.more-info-btn{background:var(--blue-bg);color:white;padding:12px 22px;border:2px solid transparent;border-radius:8px;font-size:15px;cursor:pointer;margin-top:12px;text-decoration:none;display:inline-block;transition:all 0.25s ease;}
.more-info-btn:hover{background:white;color:var(--blue-bg);border-color:var(--blue-bg);transform:translateY(-2px);}
.no-results{text-align:center;color:white;font-size:1.1em;margin-top:30px;}
</style>
</head>
<body>
<h1>📘 Book Explorer</h1>
<div class="search-container">
<input type="text" id="searchInput" placeholder="🔍 Search for a book...">
</div>
<div class="book-grid" id="bookGrid">
<?php foreach ($booksData as $b): ?>
<?php $imageLink = (!empty($b['imageLink']) && preg_match('/^https?:\/\//',$b['imageLink'])) ? $b['imageLink'] : 'https://via.placeholder.com/150x220?text=No+Image'; ?>
<div class="card" data-title="<?php echo strtolower(htmlspecialchars($b['title'])); ?>" onclick='showBook(<?php echo htmlspecialchars(json_encode($b)); ?>)'>
<img src="<?php echo htmlspecialchars($imageLink); ?>" alt="<?php echo htmlspecialchars($b['title']); ?>">
<strong><?php echo htmlspecialchars($b['title']); ?></strong>
<div class="small">✍️ <?php echo htmlspecialchars($b['author']); ?></div>
<div class="small">📅 <?php echo htmlspecialchars($b['year']); ?></div>
</div>
<?php endforeach; ?>
</div>
<p id="noResults" class="no-results" style="display:none;">❌ No matching books found.</p>
<div class="modal" id="bookModal">
<div class="modal-content">
<button class="close-btn" onclick="closeModal()">✖</button>
<img id="modalImage" src="" alt="Book Image">
<h2 id="modalTitle" style="color:var(--blue-bg);"></h2>
<p><strong>✍️ Author:</strong> <span id="modalAuthor"></span></p>
<p><strong>🌍 Country:</strong> <span id="modalCountry"></span></p>
<p><strong>🗣️ Language:</strong> <span id="modalLanguage"></span></p>
<p><strong>📖 Pages:</strong> <span id="modalPages"></span></p>
<p><strong>📅 Year:</strong> <span id="modalYear"></span></p>
<a id="modalLink" href="#" target="_blank" class="more-info-btn" style="display:none;">More Info.</a>
</div>
</div>
<script>
function showBook(book){
document.getElementById('bookModal').style.display='flex';
document.getElementById('modalTitle').textContent=book.title||'Unknown';
document.getElementById('modalAuthor').textContent=book.author||'Unknown';
document.getElementById('modalCountry').textContent=book.country||'Unknown';
document.getElementById('modalLanguage').textContent=book.language||'Unknown';
document.getElementById('modalPages').textContent=book.pages||'—';
document.getElementById('modalYear').textContent=book.year||'—';
document.getElementById('modalImage').src=(book.imageLink&&book.imageLink.startsWith('http'))?book.imageLink:'https://via.placeholder.com/150x220?text=No+Image';
if(book.link){const l=document.getElementById('modalLink');l.style.display='inline-block';l.href=book.link;}else document.getElementById('modalLink').style.display='none';
}
function closeModal(){document.getElementById('bookModal').style.display='none';}
window.onclick=e=>{if(e.target==document.getElementById('bookModal'))closeModal();}
document.getElementById('searchInput').addEventListener('input',function(){
const f=this.value.toLowerCase();
let v=0;
document.querySelectorAll('.card').forEach(c=>{c.style.display=c.dataset.title.includes(f)?(v++, 'block'):'none';});
document.getElementById('noResults').style.display=v?'none':'block';
});
</script>
</body>
</html>
