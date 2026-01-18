import { AuthProvider, useAuth } from "./auth/AuthContext";
import Login from "./pages/Login";
import Register from "./pages/Register";

function AppContent() {
    const { token, logoutUser } = useAuth();

    if (!token) {
        return (
            <div>
                <h2>Password Vault</h2>
                <Login />
                <hr />
                <Register />
            </div>
        );
    }

    return (
        <div>
            <h2>Vault Dashboard</h2>
            <p>Logged in successfully.</p>
            <button onClick={logoutUser}>Logout</button>
        </div>
    );
}

export default function App() {
    return (
        <AuthProvider>
            <AppContent />
        </AuthProvider>
    );
}
