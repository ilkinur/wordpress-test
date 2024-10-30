import React, { useState, useEffect } from 'react';
import ReactDOM from 'react-dom';

const apiUrl = '/wp-json/fs-game/v1/play';

function FsGame() {
    const [balance, setBalance] = useState(null);
    const [betAmount, setBetAmount] = useState(1);
    const [message, setMessage] = useState('');

    useEffect(() => {
        fetch('/wp-json/fs-game/v1/user_me', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': wpApiSettings.nonce,
            },
        })
            .then(res => res.json())
            .then(data => setBalance(data.balance));
    }, []);

    const handlePlay = () => {
        fetch(apiUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': wpApiSettings.nonce,
            },
            body: JSON.stringify({ bet_amount: betAmount }),
        })
        .then(res => res.json())
        .then(data => {
            if (data.balance !== undefined) {
                setBalance(data.balance);
                setMessage(data.message);
            } else {
                setMessage(data.message);
            }
        });
    };

    return (
        <div>
            <h2>Your Current Balance: {balance !== null ? `${balance} USD` : 'Loading...'}</h2>
            <select value={betAmount} onChange={e => setBetAmount(Number(e.target.value))}>
                <option value={1}>1 USD</option>
                <option value={5}>5 USD</option>
                <option value={10}>10 USD</option>
            </select>
            <button onClick={handlePlay}>Play</button>
            {message && <p>{message}</p>}
        </div>
    );
}

document.addEventListener('DOMContentLoaded', () => {
    const root = document.getElementById('fs-game-root');
    if (root) {
        ReactDOM.render(<FsGame />, root);
    }
});