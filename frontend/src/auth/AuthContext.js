import { createContext, useContext, useState } from "react";

const AuthContext = createContext(null);

export function AuthProvider({ children }) {
    const [token, setToken] = useState(localStorage.getItem("jwt"));

    function loginUser(jwt) {
        localStorage.setItem("jwt", jwt);
        setToken(jwt);
    }

    function logoutUser() {
        localStorage.removeItem("jwt");
        setToken(null);
    }

    return (
        <AuthContext.Provider value={{ token, loginUser, logoutUser }}>
            {children}
        </AuthContext.Provider>
    );
}

export function useAuth() {
    return useContext(AuthContext);
}
