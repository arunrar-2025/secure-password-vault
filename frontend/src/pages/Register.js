import { useState } from "react";
import { register } from "../api/auth";

export default function Register() {
    const [email, setEmail] = useState("");
    const [password, setPassword] = useState("");
    const [msg, setMsg] = useState("");
    const [error, setError] = useState("");

    async function handleSubmit(e) {
        e.preventDefault();
        setError("");
        setMsg("");

        try {
            await register(email, password);
            setMsg("Registered successfully. Now login.");
        } catch (err) {
            setError(err.message);
        }
    }

    return (
        <div className="container">
            <h3>Register</h3>

            {msg && <p className="success">{msg}</p>}
            {error && <p className="error">{error}</p>}

            <div className="card">
            <form onSubmit={handleSubmit}>
                <input placeholder="Email" value={email}
                       onChange={(e) => setEmail(e.target.value)} /><br /><br />

                <input type="password" placeholder="Password" value={password}
                       onChange={(e) => setPassword(e.target.value)} /><br /><br />

                <button>Register</button>
            </form>
            </div>
        </div>
    );
}
