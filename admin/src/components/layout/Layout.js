import React from 'react';
import { Layout as RALayout } from 'react-admin';
import AppBar from './AppBar';

const Layout = (props) => <RALayout {...props} appBar={AppBar} />;

export default Layout;
