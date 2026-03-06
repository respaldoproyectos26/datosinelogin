<?php
require_once __DIR__ . '/../../helpers/bootstrap.php';
require_once __DIR__ . '/../../helpers/helpers_media.php';
require_login(); csrf_check(); set_cache_headers('html-nocache');
if (!can('subir_medios')) exit('Acceso denegado');

$pageTitle='Subir material'; $pageHeader='Subir material';
require_once __DIR__ . '/../../includes/headerfiltrodatos.php';
?>
<div class="container py-4">
  <h2 class="fw-bold mb-4"><i class="bi bi-cloud-upload"></i> Subir Material</h2>
  <form id="uploadForm" enctype="multipart/form-data" method="post">
    <?php csrf_input(); ?>

    <div class="mb-3"><label class="form-label">Título</label>
      <input type="text" name="titulo" class="form-control" required></div>

    <div class="mb-3"><label class="form-label">Descripción</label>
      <textarea name="descripcion" class="form-control"></textarea></div>

    <div class="mb-3"><label class="form-label">Categoría</label>
      <input type="text" name="categoria" class="form-control"></div>

    <div class="mb-3 upload-box p-5 border rounded text-center bg-light" id="dropArea">
      <i class="bi bi-upload fs-1 text-primary"></i>
      <p class="mt-2 mb-0">Arrastra tu archivo (máx 20MB) o haz clic</p>
      <input type="file" name="archivo" id="fileInput" class="d-none">
    </div>

    <p class="text-center fw-bold my-3">ó</p>

    <div class="mb-3"><label class="form-label">Enlace externo (YouTube / Vimeo / OneDrive / Dropbox / Docs)</label>
      <input type="url" name="link" class="form-control" placeholder="https://..."></div>

    <button class="btn btn-success w-100"><i class="bi bi-cloud-arrow-up"></i> Guardar</button>
  </form>
</div>
<style>.upload-box{cursor:pointer}.upload-box:hover{background:#eef}</style>
<script>
const dropArea=document.getElementById("dropArea"),fileInput=document.getElementById("fileInput");
dropArea.addEventListener("click",()=>fileInput.click());
dropArea.addEventListener("dragover",e=>{e.preventDefault();dropArea.classList.add("border-primary")});
dropArea.addEventListener("dragleave",()=>dropArea.classList.remove("border-primary"));
dropArea.addEventListener("drop",e=>{e.preventDefault();dropArea.classList.remove("border-primary");fileInput.files=e.dataTransfer.files});

document.getElementById("uploadForm").onsubmit=async(e)=>{
  e.preventDefault();
  const fd=new FormData(e.target);
  const res=await fetch("api/upload.php",{method:"POST",headers:{'X-CSRF':'<?= csrf_token() ?>'},body:fd});
  const j=await res.json();
  if(j.ok){alert("Material registrado ✅");location.href="index.php";} else alert("Error: "+j.error);
}
</script>
<?php require_once __DIR__ . '/../../includes/footerfiltrodatos.php'; ?>
