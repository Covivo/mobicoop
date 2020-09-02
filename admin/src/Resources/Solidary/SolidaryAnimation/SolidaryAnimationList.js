import React from 'react';
import { List, Datagrid, TextField } from 'react-admin';

import { isAdmin, isSuperAdmin } from '../../../auth/permissions';

const SolidaryAnimationList = (props) => (
  <List {...props} title="Actions > liste" exporter={isSuperAdmin()} perPage={25}>
    <Datagrid>
      <TextField source="email" />
      <TextField source="givenName" />
      <TextField source="familyName" />
    </Datagrid>
  </List>
);

export default SolidaryAnimationList;
