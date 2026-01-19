import API_BASE from "./config";

function authHeader(token) {
    return {
        "Content-Type": "application/json",
        "Authorization": "Bearer " + token
    };
}

export async function addVaultEntry(token, data) {
    const res = await fetch(`${API_BASE}/?route=vault_add`, {
        method: "POST",
        headers: authHeader(token),
        body: JSON.stringify(data)
    });
    const json = await res.json();
    if (!res.ok) throw new Error(json.error || "Add failed");
    return json;
}

export async function listVault(token) {
    const res = await fetch(`${API_BASE}/?route=vault_list`, {
        headers: authHeader(token)
    });
    const json = await res.json();
    if (!res.ok) throw new Error(json.error || "List failed");
    return json;
}

export async function getVaultEntry(token, id, master) {
    const res = await fetch(`${API_BASE}/?route=vault_get`, {
        method: "POST",
        headers: authHeader(token),
        body: JSON.stringify({ id, master })
    });
    const json = await res.json();
    if (!res.ok) throw new Error(json.error || "Decrypt failed");
    return json;
}

export async function deleteVaultEntry(token, id) {
    const res = await fetch(`${API_BASE}/?route=vault_delete`, {
        method: "POST",
        headers: authHeader(token),
        body: JSON.stringify({ id })
    });
    const json = await res.json();
    if (!res.ok) throw new Error(json.error || "Delete failed");
    return json;
}
