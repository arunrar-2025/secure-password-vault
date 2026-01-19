import { useState } from "react";
import { login } from "../api/auth";
import { useAuth } from "../auth/AuthContext";

export default function Login() {
    const { loginUser } = useAuth();

    const [email, setEmail] = useState("");
    const [password, setPassword] = useState("");
    const [error, setError] = useState("");

    async function handleSubmit(e) {
        e.preventDefault();
        setError("");

        try {
            const token = await login(email, password);
            loginUser(token);
        } catch (err) {
            setError(err.message);
        }
    }

    return (
        <div className="container">
            <h3>Login</h3>

            {error && <p className="error">{error}</p>}

            <div className="card">
            <form onSubmit={handleSubmit}>
                <input placeholder="Email" value={email}
                       onChange={(e) => setEmail(e.target.value)} /><br /><br />

                <input type="password" placeholder="Password" value={password}
                       onChange={(e) => setPassword(e.target.value)} /><br /><br />

                <button>Login</button>
            </form>
            </div>
        </div>
    );
}
