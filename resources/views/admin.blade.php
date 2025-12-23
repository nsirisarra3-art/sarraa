<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin - Gestion Agence de Voyage</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/css/admin.css" rel="stylesheet">
</head>
<body class="p-4">
<div class="container">
    <h1 class="mb-4">Gestion Agence de Voyage</h1>

    <ul class="nav nav-tabs mb-3" id="tabs">
        <li class="nav-item"><a class="nav-link active" data-target="trips">Voyages</a></li>
        <li class="nav-item"><a class="nav-link" data-target="customers">Clients</a></li>
        <li class="nav-item"><a class="nav-link" data-target="bookings">Réservations</a></li>
    </ul>

    <div id="authArea"></div>
    <div id="content" style="display:none;"></div>
</div>

<template id="list-template">
    <div>
        <div class="d-flex justify-content-between mb-2">
            <h3 id="title"></h3>
            <button class="btn btn-primary" id="createBtn">Créer</button>
        </div>
        <div id="formArea" style="display:none;" class="mb-3"></div>
        <table class="table table-striped">
            <thead id="thead"></thead>
            <tbody id="tbody"></tbody>
        </table>
    </div>
</template>

<script>
const apiBase = '/api';

function qs(sel, ctx=document) { return ctx.querySelector(sel); }

const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

async function fetchJson(url, opts={}){
    opts.credentials = 'same-origin';
    opts.headers = opts.headers || {};
    if (opts.method && opts.method.toUpperCase() !== 'GET') {
        opts.headers['X-CSRF-TOKEN'] = csrfToken;
    }
    if (opts.body && typeof opts.body === 'object' && !(opts.body instanceof FormData)) {
        opts.headers['Content-Type'] = 'application/json';
        opts.body = JSON.stringify(opts.body);
    }
    const res = await fetch(url, opts);
    if (res.status === 204) return null;
    return res.json();
}

function createListView(kind) {
    const tpl = document.getElementById('list-template').content.cloneNode(true);
    qs('#title', tpl).textContent = ({trips:'Voyages', customers:'Clients', bookings:'Réservations'})[kind];
    const createBtn = qs('#createBtn', tpl);
    const formArea = qs('#formArea', tpl);
    const thead = qs('#thead', tpl);
    const tbody = qs('#tbody', tpl);

    async function fetchItems(){
        return await fetchJson(`${apiBase}/${kind}`);
    }

    function row(columns, item){
        const tr = document.createElement('tr');
        columns.forEach(c => { const td = document.createElement('td'); td.innerHTML = item[c] ?? ''; tr.appendChild(td); });
        const actions = document.createElement('td');
        const edit = document.createElement('button'); edit.className='btn btn-sm btn-secondary me-2'; edit.textContent='Modifier';
        const del = document.createElement('button'); del.className='btn btn-sm btn-danger'; del.textContent='Supprimer';
        edit.onclick = () => showForm(item);
        del.onclick = async () => { if(!confirm('Supprimer ?')) return; await fetchJson(`${apiBase}/${kind}/${item.id}`, {method:'DELETE'}); await reload(); };
        actions.appendChild(edit); actions.appendChild(del); tr.appendChild(actions);
        return tr;
    }

    function clearForm(){ formArea.innerHTML=''; formArea.style.display='none'; }

    function showForm(item){
        formArea.style.display='block'; formArea.innerHTML='';
        const form = document.createElement('form');
        form.className='card card-body mb-3';
        const fields = (kind==='trips')?[
            ['title','Titre'],['description','Description'],['destination','Destination'],['price','Prix'],['start_date','Date début'],['end_date','Date fin']
        ] : (kind==='customers')?[
            ['name','Nom'],['email','Email'],['phone','Téléphone']
        ] : [
            ['trip_id','Voyage (ID)'],['customer_id','Client (ID)'],['seats','Places']
        ];

        fields.forEach(([name,label])=>{
            const div = document.createElement('div'); div.className='mb-2';
            const lbl = document.createElement('label'); lbl.textContent = label; lbl.className='form-label';
            const inp = document.createElement('input'); inp.name=name; inp.className='form-control'; inp.value = item?.[name] ?? '';
            div.appendChild(lbl); div.appendChild(inp); form.appendChild(div);
        });

        const save = document.createElement('button'); save.className='btn btn-primary me-2'; save.textContent='Enregistrer';
        const cancel = document.createElement('button'); cancel.className='btn btn-secondary'; cancel.textContent='Annuler';
        save.onclick = async (e)=>{
            e.preventDefault();
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());
            if(item && item.id){ await fetchJson(`${apiBase}/${kind}/${item.id}`, {method:'PUT', body:data}); }
            else { await fetchJson(`${apiBase}/${kind}`, {method:'POST', body:data}); }
            clearForm(); await reload();
        };
        cancel.onclick = (e)=>{ e.preventDefault(); clearForm(); };
        form.appendChild(save); form.appendChild(cancel);
        formArea.appendChild(form);
    }

    async function reload(){
        const items = await fetchItems();
        tbody.innerHTML='';
        let columns = [];
        if(kind==='trips') columns = ['id','title','destination','price','start_date','end_date'];
        if(kind==='customers') columns = ['id','name','email','phone'];
        if(kind==='bookings') columns = ['id','trip_id','customer_id','seats','total_price','status'];
        thead.innerHTML = '<tr>' + columns.map(c=>`<th>${c}</th>`).join('') + '<th>Actions</th></tr>';
        items.forEach(item=> tbody.appendChild(row(columns, item)));
    }

    createBtn.onclick = ()=> showForm(null);
    reload();

    return tpl;
}

function mount(kind){
    const content = document.getElementById('content'); content.innerHTML='';
    content.appendChild(createListView(kind));
}

document.addEventListener('click', (ev)=>{
    const a = ev.target.closest('[data-target]');
    if(!a) return;
    ev.preventDefault();
    document.querySelectorAll('#tabs .nav-link').forEach(n=>n.classList.remove('active'));
    a.classList.add('active');
    mount(a.getAttribute('data-target'));
});

// auth + initial mount
async function checkAuthAndMount(){
    const authArea = document.getElementById('authArea');
    const content = document.getElementById('content');
    try{
        const user = await fetchJson('/auth/user');
        if(!user || Object.keys(user).length === 0){
            // show login/register
            authArea.innerHTML = `
                <div class="card mb-3"><div class="card-body">
                <h4>Connexion / Inscription</h4>
                <div class="row">
                    <div class="col-md-6">
                        <h5>Connexion</h5>
                        <form id="loginForm">
                            <div class="mb-2"><input name="email" placeholder="Email" class="form-control"></div>
                            <div class="mb-2"><input type="password" name="password" placeholder="Mot de passe" class="form-control"></div>
                            <button class="btn btn-primary">Se connecter</button>
                        </form>
                    </div>
                    <div class="col-md-6">
                        <h5>Inscription</h5>
                        <form id="registerForm">
                            <div class="mb-2"><input name="name" placeholder="Nom" class="form-control"></div>
                            <div class="mb-2"><input name="email" placeholder="Email" class="form-control"></div>
                            <div class="mb-2"><input type="password" name="password" placeholder="Mot de passe" class="form-control"></div>
                            <div class="mb-2"><input type="password" name="password_confirmation" placeholder="Confirmer mot de passe" class="form-control"></div>
                            <button class="btn btn-success">S'inscrire</button>
                        </form>
                    </div>
                </div>
                </div></div>
            `;

            qs('#loginForm').onsubmit = async (e)=>{
                e.preventDefault();
                const data = Object.fromEntries(new FormData(e.target).entries());
                const res = await fetchJson('/login', {method:'POST', body:data});
                if(res && res.email){ authArea.innerHTML=''; content.style.display='block'; mount('trips'); }
                else alert('Erreur de connexion');
            };

            qs('#registerForm').onsubmit = async (e)=>{
                e.preventDefault();
                const data = Object.fromEntries(new FormData(e.target).entries());
                const res = await fetchJson('/register', {method:'POST', body:data});
                if(res && res.email){ authArea.innerHTML=''; content.style.display='block'; mount('trips'); }
                else alert('Erreur d\'inscription');
            };
        } else {
            // authenticated
            authArea.innerHTML = `<div class="mb-3">Connecté en tant que <strong>${user.email}</strong> <button id="logoutBtn" class="btn btn-sm btn-outline-secondary ms-2">Se déconnecter</button></div>`;
            content.style.display='block';
            mount('trips');
            qs('#logoutBtn').onclick = async ()=>{ await fetchJson('/logout', {method:'POST'}); window.location.reload(); };
        }
    } catch(err){ console.error(err); authArea.textContent = 'Erreur de vérification d\'authentification'; }
}

checkAuthAndMount();
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
