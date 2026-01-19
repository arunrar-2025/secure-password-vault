import { useEffect, useState } from "react";
import { useAuth } from "../auth/AuthContext";
import {
    addVaultEntry,
    listVault,
    getVaultEntry,
    deleteVaultEntry
} from "../api/vault";

export default function Vault() {
    const { token, logoutUser } = useAuth();

    const [master, setMaster] = useState("");
    const [entries, setEntries] = useState([]);
    const [selected, setSelected] = useState(null);
    const [error, setError] = useState("");

    // form fields
    const [title, setTitle] = useState("");
    const [username, setUsername] = useState("");
    const [password, setPassword] = useState("");
    const [url, setUrl] = useState("");
    const [notes, setNotes] = useState("");
    const [search, setSearch] = useState("");

    async function loadEntries() {
        try {
            const data = await listVault(token);
            setEntries(data);
        } catch (err) {
            setError(err.message);
        }
    }

    useEffect(() => {
        loadEntries();
    }, []);

    async function handleAdd(e) {
        e.preventDefault();
        setError("");

        try {
            await addVaultEntry(token, {
                master,
                title,
                username,
                password,
                url,
                notes
            });
            setTitle(""); setUsername(""); setPassword(""); setUrl(""); setNotes("");
            loadEntries();
        } catch (err) {
            setError(err.message);
        }
    }

    async function handleDecrypt(id) {
        setError("");
        try {
            const data = await getVaultEntry(token, id, master);
            setSelected(data);
        } catch (err) {
            setError(err.message);
        }
    }

    async function handleDelete(id) {
        await deleteVaultEntry(token, id);
        setSelected(null);
        loadEntries();
    }

    return (
        <div>
            <h2>Password Vault</h2>
            <button onClick={logoutUser}>Logout</button>

            <hr />

            <h4>Master Password</h4>
            <input
                type="password"
                placeholder="Enter master password"
                value={master}
                onChange={(e) => setMaster(e.target.value)}
            />

            <hr />

            <h3>Add New Entry</h3>
            {error && <p style={{color:"red"}}>{error}</p>}

            <form onSubmit={handleAdd}>
                <input placeholder="Title" value={title} onChange={(e)=>setTitle(e.target.value)} /><br/>
                <input placeholder="Username" value={username} onChange={(e)=>setUsername(e.target.value)} /><br/>
                <input placeholder="Password" value={password} onChange={(e)=>setPassword(e.target.value)} /><br/>
                <input placeholder="URL" value={url} onChange={(e)=>setUrl(e.target.value)} /><br/>
                <textarea placeholder="Notes" value={notes} onChange={(e)=>setNotes(e.target.value)} /><br/>
                <button>Add</button>
            </form>

            <hr />

            <h4>Search</h4>
            <input placeholder="Search by title..." value={search} onChange={(e) => setSearch(e.target.value)}/>

            <h3>Stored Entries</h3>
            <ul>
                {entries
                    .filter(e => e.title.toLowerCase().includes(search.toLowerCase()))
                    .map(e => (
                    <li key={e.id}>
                        {e.title}
                        <button onClick={()=>handleDecrypt(e.id)}>Open</button>
                        <button onClick={()=>handleDelete(e.id)}>Delete</button>
                    </li>
                ))}
            </ul>

            {selected && (
                <>
                    <hr />
                    <h3>Decrypted Entry</h3>
                    <p><b>Title:</b> {selected.title}</p>
                    <p>
                        <b>Username:</b> {selected.username}
                        <button onClick={() => navigator.clipboard.writeText(selected.username)}>Copy</button>
                    </p>
                    <p>
                        <p>Password:</p> {selected.password}
                        <button onClick={() => navigator.clipboard.writeText(selected.password)}>Copy</button>
                    </p>
                    <p><b>URL:</b> {selected.url}</p>
                    <p><b>Notes:</b> {selected.notes}</p>
                </>
            )}
        </div>
    );
}
