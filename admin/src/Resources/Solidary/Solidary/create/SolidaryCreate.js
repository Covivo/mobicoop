import React from 'react';
import { Create } from 'react-admin';

import SolidaryForm from './SolidaryForm';

const solidaryAskDefaultValues = {
  status: 0,
  days: { mon: false, tue: false, wed: false, thu: false, fri: false, sat: false, sun: false },
};

const SolidaryCreate = (props) => (
  <Create {...props}>
    <SolidaryForm initialValues={solidaryAskDefaultValues} />
  </Create>
);

export default SolidaryCreate;
