<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// archive.php
include __DIR__ . "/../db.php"; // umakyat sa parent folder

$selectedDept = isset($_GET['dept']) ? intval($_GET['dept']) : 0;

/* Departments + Archive Count */
$department = $conn->query("
SELECT d.*, 
(SELECT COUNT(*) FROM archived_items a WHERE a.department_id=d.id) as total 
FROM departments d ORDER BY d.name ASC
");

/* Archive Items */
$where = $selectedDept ? "WHERE department_id=$selectedDept" : "";
$items = $conn->query("
SELECT * FROM archived_items 
$where 
ORDER BY archived_at DESC
");
?>
<!DOCTYPE html>
<html>
<head>
<title>Archive - Eufile</title>
<style>
body{
    margin:0;
    font-family:Segoe UI;
    display:flex;
    background:#111827;
    color:#fff;
}
aside{
    width:250px;
    backdrop-filter:blur(12px);
    background:rgba(255,255,255,0.05);
    border-right:1px solid rgba(255,255,255,0.1);
    display:flex;
    flex-direction:column;
    justify-content:space-between;
}
.brand{
    padding:20px;
    font-size:22px;
    font-weight:bold;
}
.menu-category{
    padding:10px 20px;
    font-size:12px;
    opacity:.6;
    text-transform:uppercase;
}
.nav-item{
    padding:12px 20px;
    cursor:pointer;
    display:flex;
    align-items:center;
    justify-content:space-between;
}
.nav-item:hover{
    background:rgba(255,255,255,0.08);
}
.nav-item.active{
    background:rgba(37,99,235,0.5);
}
.sub-item{
    padding-left:40px;
    font-size:14px;
}
.badge-count{
    background:#2563eb;
    padding:3px 8px;
    border-radius:20px;
    font-size:12px;
}
main{
    flex:1;
    padding:30px;
}
.page-title{
    font-size:28px;
    margin-bottom:20px;
}
.search-box{
    margin-bottom:20px;
}
.search-box input{
    width:100%;
    padding:12px;
    border-radius:10px;
    border:none;
    outline:none;
}
.table{
    width:100%;
    border-collapse:collapse;
    background:rgba(255,255,255,0.08);
    backdrop-filter:blur(10px);
    border-radius:15px;
    overflow:hidden;
}
.table th, .table td{
    padding:14px;
}
.table tr{
    border-bottom:1px solid rgba(255,255,255,0.1);
}
.table tr:hover{
    background:rgba(255,255,255,0.05);
}
.restore-btn{
    background:#10b981;
    border:none;
    padding:6px 12px;
    border-radius:8px;
    color:white;
    cursor:pointer;
}
.modal{
    display:none;
    position:fixed;
    top:0; left:0;
    width:100%; height:100%;
    background:rgba(0,0,0,0.6);
    justify-content:center;
    align-items:center;
}
.modal-content{
    background:#fff;
    color:#000;
    padding:25px;
    border-radius:15px;
    width:400px;
}
.close{
    float:right;
    cursor:pointer;
    font-weight:bold;
}
</style>

<script>
function toggleDropdown(){
    const drop = document.getElementById("deptDropdown");
    drop.style.display = drop.style.display==="block"?"none":"block";
}

/* Live Search */
function searchArchive(){
    let input = document.getElementById("searchInput").value.toLowerCase();
    let rows = document.querySelectorAll(".archive-row");
    rows.forEach(row=>{
        let text = row.innerText.toLowerCase();
        row.style.display = text.includes(input) ? "" : "none";
    });
}

/* AJAX Restore */
function restoreItem(id){
    if(!confirm("Restore this item?")) return;

    fetch("restore.php",{
        method:"POST",
        headers:{"Content-Type":"application/x-www-form-urlencoded"},
        body:"id="+id
    })
    .then(res=>res.json())
    .then(data=>{
        if(data.success){
            document.getElementById("row-"+id).remove();
        }else{
            alert("Restore failed");
        }
    });
}

/* Folder Preview */
function previewFolder(name){
    document.getElementById("modalTitle").innerText=name;
    document.getElementById("previewModal").style.display="flex";
}
function closeModal(){
    document.getElementById("previewModal").style.display="none";
}
</script>
</head>
<body>

<!-- SIDEBAR -->
<aside>
<div>
<div class="brand">☁️ Eufile</div>

<div class="menu-category">Workspace</div>

<div class="nav-item" onclick="toggleDropdown()">
🏢 Departments <span>▼</span>
</div>

<div id="deptDropdown" style="display:none;">
<?php while($dept=$departments->fetch_assoc()): ?>
<a href="?dept=<?=$dept['id']?>" style="text-decoration:none;color:white;">
<div class="nav-item sub-item">
<?=$dept['name']?>
<span class="badge-count"><?=$dept['total']?></span>
</div>
</a>
<?php endwhile; ?>
</div>

<div class="menu-category">System</div>
<div class="nav-item active">📦 Archive</div>
</div>

<div style="padding:20px;">
<button style="width:100%;padding:10px;border:none;border-radius:8px;background:#ef4444;color:white;">
Log Out
</button>
</div>
</aside>

<!-- MAIN -->
<main>
<div class="page-title">📦 Archive</div>

<div class="search-box">
<input type="text" id="searchInput" onkeyup="searchArchive()" placeholder="Search archived files...">
</div>

<table class="table">
<tr>
<th>Name</th>
<th>Type</th>
<th>Date</th>
<th>Action</th>
</tr>

<?php while($row=$items->fetch_assoc()): ?>
<tr class="archive-row" id="row-<?=$row['id']?>">
<td onclick="<?php if($row['type']=='folder') echo "previewFolder('".$row['name']."')"; ?>">
<?=$row['name']?>
</td>
<td><?=ucfirst($row['type'])?></td>
<td><?=date("M d, Y",strtotime($row['archived_at']))?></td>
<td>
<button class="restore-btn" onclick="restoreItem(<?=$row['id']?>)">Restore</button>
</td>
</tr>
<?php endwhile; ?>

</table>
</main>

<!-- Folder Preview Modal -->
<div class="modal" id="previewModal">
<div class="modal-content">
<span class="close" onclick="closeModal()">X</span>
<h3 id="modalTitle"></h3>
<p>This is a preview of the archived folder.</p>
</div>
</div>

</body>
</html>