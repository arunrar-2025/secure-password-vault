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
        <div>
            <h3>Register</h3>

            {msg && <p style={{ color: "green" }}>{msg}</p>}
            {error && <p style={{ color: "red" }}>{error}</p>}

            <form onSubmit={handleSubmit}>
                <input placeholder="Email" value={email}
                       onChange={(e) => setEmail(e.target.value)} /><br /><br />

                <input type="password" placeholder="Password" value={password}
                       onChange={(e) => setPassword(e.target.value)} /><br /><br />

                <button>Register</button>
            </form>
        </div>
    );
}
