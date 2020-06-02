import React from 'react';
import ReactDOM from 'react-dom';
import './index.css';
import App from './App';
import registerServiceWorker from './registerServiceWorker';

require('dotenv').config();

ReactDOM.render(<App />, global.document.getElementById('root'));
registerServiceWorker();
