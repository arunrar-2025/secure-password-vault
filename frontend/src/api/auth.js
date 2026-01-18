import API_BASE from "./config";

export async function login(email, password) {
    const res = await fetch(`${API_BASE}/?route=login`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ email, password })
    });

    const data = await res.json();

    if (!res.ok) {
        throw new Error(data.error || "Login failed");
    }

    return data.token;
}

export async function register(email, password) {
    const res = await fetch(`${API_BASE}/?route=register`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ email, password })
    });

    const data = await res.json();

    if (!res.ok) {
        throw new Error(data.error || "Registration failed");
    }

    return true;
}
