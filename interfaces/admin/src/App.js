import React from 'react';
import { HydraAdmin } from '@api-platform/admin';
require('dotenv').config();

export default () => <HydraAdmin entrypoint= {process.env.REACT_APP_API} />;