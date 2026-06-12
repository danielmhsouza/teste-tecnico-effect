<template>
    <div id="root">
        <nav>
            <router-link to="/clients">Clientes</router-link>
            <router-link to="/services">Serviços</router-link>
            <router-link to="/contracts">Contratos</router-link>
            <router-link to="/strategies">Descontos</router-link>
        </nav>
        <main>
            <router-view />
        </main>
    </div>
</template>

<style>
* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

body {
    font-family: system-ui, sans-serif;
    background: #f5f5f5;
    color: #222;
}

#root {
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}

nav {
    background: #1a1a2e;
    padding: 1rem 2rem;
    display: flex;
    gap: 1.5rem;
}

nav a {
    color: #e0e0ff;
    text-decoration: none;
    font-weight: 500;
}

nav a.router-link-active {
    color: #7b8cde;
    border-bottom: 2px solid #7b8cde;
}

main {
    padding: 2rem;
    max-width: 1100px;
    margin: 0 auto;
    width: 100%;
}

table {
    width: 100%;
    border-collapse: collapse;
    background: #fff;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 1px 4px rgba(0, 0, 0, .08);
}

th,
td {
    padding: .75rem 1rem;
    text-align: left;
    border-bottom: 1px solid #eee;
}

th {
    background: #f0f0f8;
    font-size: .85rem;
    text-transform: uppercase;
    letter-spacing: .05em;
}

button {
    cursor: pointer;
    padding: .45rem .9rem;
    border: none;
    border-radius: 4px;
    font-size: .9rem;
}

.btn-primary {
    background: #1a1a2e;
    color: #fff;
}

.btn-danger {
    background: #c0392b;
    color: #fff;
}

.btn-warning {
    background: #e67e22;
    color: #fff;
}

.btn-sm {
    padding: .3rem .65rem;
    font-size: .8rem;
}

input,
select {
    padding: .5rem .75rem;
    border: 1px solid #ccc;
    border-radius: 4px;
    width: 100%;
    font-size: .95rem;
}

label {
    display: block;
    font-size: .85rem;
    margin-bottom: .25rem;
    font-weight: 500;
}

.form-group {
    margin-bottom: 1rem;
}

.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, .45);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 100;
}

.modal {
    background: #fff;
    border-radius: 8px;
    padding: 2rem;
    min-width: 380px;
    max-width: 520px;
    width: 100%;
}

.modal h3 {
    margin-bottom: 1.25rem;
}

.modal-actions {
    display: flex;
    gap: .75rem;
    justify-content: flex-end;
    margin-top: 1.25rem;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.25rem;
}

.pagination {
    display: flex;
    gap: .5rem;
    margin-top: 1rem;
    align-items: center;
}

.pagination button:disabled {
    opacity: .4;
    cursor: default;
}

.badge {
    display: inline-block;
    padding: .2rem .55rem;
    border-radius: 12px;
    font-size: .78rem;
    font-weight: 600;
}

.badge-active {
    background: #d4edda;
    color: #155724;
}

.badge-inactive {
    background: #e2e3e5;
    color: #383d41;
}

.badge-canceled {
    background: #f8d7da;
    color: #721c24;
}

.error-msg {
    color: #c0392b;
    font-size: .85rem;
    margin-bottom: .75rem;
}
</style>
