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

$books = json_decode(file_get_contents("books.json"), true) ?: die("❌ Error: Unable to load books.json");
$fiction = $nonFiction = [];

foreach ($books as $book) {
    $title = strtolower($book['title']);
    $book['type'] = preg_match('/history|science|diary|biography|philosophy/', $title) ? 'Non-Fiction' : 'Fiction';
    ${$book['type'] === 'Fiction' ? 'fiction' : 'nonFiction'}[] = new Node($book);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>📘 Library</title>
<style>
body {
  font-family: Poppins, sans-serif;
  background: linear-gradient(135deg, #0f0f0f, #1e1e1e, #2c2c2c);
  color: #e5e5e5;
  margin: 0;
  padding: 0;
  min-height: 100vh;
}

header {
  text-align: center;
  padding: 40px 20px 10px;
  text-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);
}

h1 {
  font-size: 2.5em;
  margin: 0 0 10px;
  color: #f1f1f1;
}

h2 {
  text-align: center;
  margin: 40px 0 20px;
  color: #c0c0c0;
  text-shadow: 0 2px 5px rgba(0, 0, 0, 0.5);
}

.search-container {
  text-align: center;
  margin: 40px 0;
}

#searchInput {
  width: 80%;
  max-width: 800px;
  padding: 14px 18px;
  font-size: 17px;
  border: none;
  border-radius: 50px;
  outline: none;
  background: #2a2a2a;
  color: #f0f0f0;
  transition: all 0.3s;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.5);
  display: block;
  margin: 0 auto;
}

#searchInput::placeholder {
  color: #9ca3af;
}

#searchInput:focus {
  background: #3a3a3a;
  transform: scale(1.02);
  box-shadow: 0 0 15px #555;
}

.book-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(230px, 1fr));
  gap: 25px;
  padding: 0 50px 60px;
}

.card {
  background: #1c1c1c;
  border-radius: 15px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4);
  text-align: center;
  overflow: hidden;
  transition: transform 0.3s, box-shadow 0.3s;
  cursor: pointer;
}

.card:hover {
  transform: translateY(-10px);
  box-shadow: 0 12px 30px rgba(0, 0, 0, 0.6);
}

.card img {
  width: 100%;
  height: 250px;
  object-fit: cover;
}

.card-content {
  padding: 15px;
}

.card-content h3 {
  font-size: 1.1em;
  margin: 10px 0 5px;
  color: #f3f3f3;
}

.card-content p {
  font-size: 0.9em;
  color: #b0b0b0;
}

.modal {
  display: none;
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.75);
  justify-content: center;
  align-items: center;
  z-index: 999;
}

.modal-content {
  background: linear-gradient(135deg, #1f1f1f, #2a2a2a);
  border: 2px solid #444;
  border-radius: 16px;
  padding: 25px;
  width: 90%;
  max-width: 420px;
  text-align: center;
  color: #f8fafc;
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.6);
}

.modal-content img {
  width: 100%;
  height: 260px;
  object-fit: cover;
  border-radius: 10px;
  margin-bottom: 15px;
}

.close-btn {
  position: absolute;
  top: 12px;
  right: 15px;
  background: #ef4444;
  border: none;
  color: white;
  font-size: 18px;
  padding: 6px 10px;
  border-radius: 6px;
  cursor: pointer;
}

.more-info-btn {
  background: linear-gradient(135deg, #3b82f6, #1e3a8a);
  color: #fff;
  padding: 10px 18px;
  border: none;
  border-radius: 8px;
  font-size: 15px;
  cursor: pointer;
  margin-top: 12px;
  display: inline-block;
  text-decoration: none;
  transition: 0.3s;
}

.more-info-btn:hover {
  background: linear-gradient(135deg, #60a5fa, #2563eb);
}

.no-results {
  text-align: center;
  color: #aaa;
  font-size: 1.1em;
  margin-top: 30px;
}

</style>
</head>
<body>
<header><h1>📚 Library Collection</h1></header>
<div class="search-container"><input type="text" id="searchInput" placeholder="🔍 Search for a book, author, or type..."></div>

<?php foreach (['Fiction'=>$fiction,'Non-Fiction'=>$nonFiction] as $label=>$nodes): ?>
<h2><?= $label ?> Books</h2>
<div class="book-grid">
<?php foreach ($nodes as $node): $b=$node->data; ?>
<div class="card" data-title="<?= strtolower($b['title'].' '.$b['type']) ?>" onclick='showBook(<?= json_encode($b) ?>)'>
<img src="<?= htmlspecialchars($b['imageLink']) ?>" alt="<?= htmlspecialchars($b['title']) ?>">
<div class="card-content">
<h3><?= htmlspecialchars($b['title']) ?></h3>
<p><?= htmlspecialchars($b['author']) ?></p>
<p><em><?= htmlspecialchars($b['type']) ?></em></p>
</div>
</div>
<?php endforeach; ?>
</div>
<?php endforeach; ?>

<p id="noResults" class="no-results" style="display:none;">❌ No books found.</p>

<div class="modal" id="bookModal">
  <div class="modal-content">
    <button class="close-btn" onclick="closeModal()">✖</button>
    <img id="modalImage" src="" alt="Book Image">
    <h2 id="modalTitle"></h2>
    <p><strong>👤 Author:</strong> <span id="modalAuthor"></span></p>
    <p><strong>🌍 Country:</strong> <span id="modalCountry"></span></p>
    <p><strong>🗣️ Language:</strong> <span id="modalLanguage"></span></p>
    <p><strong>📖 Pages:</strong> <span id="modalPages"></span></p>
    <p><strong>📅 Year:</strong> <span id="modalYear"></span></p>
    <p><strong>📂 Type:</strong> <span id="modalType"></span></p>
    <a id="modalLink" href="#" target="_blank" class="more-info-btn" style="display:none;">More Info ↗</a>
  </div>
</div>

<script>
const modal=document.getElementById('bookModal'),mImage=document.getElementById('modalImage'),mTitle=document.getElementById('modalTitle'),
mAuthor=document.getElementById('modalAuthor'),mCountry=document.getElementById('modalCountry'),mLanguage=document.getElementById('modalLanguage'),
mPages=document.getElementById('modalPages'),mYear=document.getElementById('modalYear'),mType=document.getElementById('modalType'),
mLink=document.getElementById('modalLink'),noRes=document.getElementById('noResults');

function showBook(b){
  modal.style.display='flex';
  mTitle.textContent=b.title; mAuthor.textContent=b.author; mCountry.textContent=b.country||'—';
  mLanguage.textContent=b.language||'—'; mPages.textContent=b.pages||'—'; mYear.textContent=b.year||'—';
  mType.textContent=b.type; mImage.src=b.imageLink||'https://via.placeholder.com/150x220?text=No+Image';
  if(b.link){ mLink.href=b.link; mLink.style.display='inline-block'; }else mLink.style.display='none';
}
function closeModal(){ modal.style.display='none'; }
window.onclick=e=>{if(e.target==modal) closeModal();}

document.getElementById('searchInput').addEventListener('input',function(){
  let f=this.value.toLowerCase(),v=0;
  document.querySelectorAll('.card').forEach(c=>{c.style.display=c.dataset.title.includes(f)?(v++, 'block'):'none';});
  noRes.style.display=v?'none':'block';
});
</script>
</body>
</html>
