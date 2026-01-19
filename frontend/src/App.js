import { AuthProvider, useAuth } from "./auth/AuthContext";
import Login from "./pages/Login";
import Register from "./pages/Register";
import Vault from "./pages/Vault";

function AppContent() {
    const { token } = useAuth();

    // If not logged in → show Auth pages
    if (!token) {
        return (
            <div className="card">
                <h2>Secure Password Vault</h2>
                <Login />
                <hr />
                <Register />
            </div>
        );
    }

    // If logged in → show Vault
    return <Vault />;
}

export default function App() {
    return (
        <AuthProvider>
            <AppContent />
        </AuthProvider>
    );
}
