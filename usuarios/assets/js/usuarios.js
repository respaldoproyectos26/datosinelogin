$(function(){
  const tbl = $('#tblUsuarios').DataTable({
    processing:true,
    serverSide:true,
    responsive:true,
    ajax:{ url:'api/list.php', type:'POST' },
    language: {
      url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-MX.json'
    },
    columns:[
      {data:'id'},
      {data:'usuario'},
      {data:'estado'},
      {data:'created_at'},
      {
        data:null,
        orderable:false,
        searchable:false,
        render:(row)=>{
          const id = row.id;
          return `
            <div class="d-flex gap-1">
              <button class="btn btn-sm btn-success btnEdit" data-id="${id}">Editar</button>
              <button class="btn btn-sm btn-danger btnDel" data-id="${id}">Eliminar</button>
            </div>`;
        }
      }
    ]
  });

  const modalEl = document.getElementById('userModal');
  const modal = new bootstrap.Modal(modalEl);

  $('#btnNuevo').on('click', ()=>{
    $('#userModalTitle').text('Nuevo usuario');
    $('#u_id').val('');
    $('#u_usuario').val('');
    $('#u_estado').val('SAN LUIS POTOSI');
    $('#u_password').val('');
    modal.show();
  });

  $('#tblUsuarios').on('click','.btnEdit', function(){
    const row = tbl.row($(this).closest('tr')).data();
    $('#userModalTitle').text('Editar usuario');
    $('#u_id').val(row.id);
    $('#u_usuario').val(row.usuario);
    $('#u_estado').val(row.estado || '');
    $('#u_password').val(''); // vacío para no cambiarla
    modal.show();
  });

  $('#userForm').on('submit', async function(e){
    e.preventDefault();
    const fd = new FormData(this);

    try{
      const r = await fetch('api/save.php', { method:'POST', body: fd, credentials:'same-origin' });
      const j = await r.json();
      Swal.fire({ icon: j.success?'success':'error', title: j.message || 'Resultado', timer: 1800, showConfirmButton:false });
      if (j.success) { modal.hide(); tbl.ajax.reload(null,false); }
    }catch(err){
      Swal.fire({ icon:'error', title:'Error', text: err.message });
    }
  });

  $('#tblUsuarios').on('click','.btnDel', async function(){
    const id = this.dataset.id;

    const ok = await Swal.fire({
      icon:'warning',
      title:'Eliminar usuario',
      text:'Esto lo borra de la base. ¿Continuar?',
      showCancelButton:true,
      confirmButtonText:'Sí, eliminar',
      cancelButtonText:'Cancelar'
    });

    if (!ok.isConfirmed) return;

    // CSRF: toma el token del hidden del form (ya existe en el DOM)
    const csrf = document.querySelector('#userForm input[name="csrf_token"]')?.value || '';

    const fd = new FormData();
    fd.append('id', id);
    fd.append('csrf_token', csrf);

    try{
      const r = await fetch('api/delete.php', { method:'POST', body: fd, credentials:'same-origin' });
      const j = await r.json();
      Swal.fire({ icon: j.success?'success':'error', title: j.message || 'Resultado', timer: 1800, showConfirmButton:false });
      if (j.success) tbl.ajax.reload(null,false);
    }catch(err){
      Swal.fire({ icon:'error', title:'Error', text: err.message });
    }
  });
});
