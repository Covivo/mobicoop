import React from 'react';
import { HydraAdmin } from '@api-platform/admin';
require('dotenv').config();

export default () => <HydraAdmin entrypoint= "http://localhost:8080/doc" />;