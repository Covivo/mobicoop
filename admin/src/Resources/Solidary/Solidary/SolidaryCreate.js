import React from 'react';
import { Create } from 'react-admin';
import SolidaryForm from './SolidaryForm';

const SolidaryCreate = (props) => (
  <Create {...props}>
    <SolidaryForm />
  </Create>
);

export default SolidaryCreate;
