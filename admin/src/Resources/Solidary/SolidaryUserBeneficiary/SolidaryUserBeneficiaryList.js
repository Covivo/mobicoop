import React from 'react';

import { List, Datagrid, TextField } from 'react-admin';

export const SolidaryUserVolunteerList = (props) => (
  <List {...props} title="Demandeurs solidaires > liste" perPage={25}>
    <Datagrid>
      <TextField source="email" />
      <TextField source="givenName" />
      <TextField source="familyName" />
    </Datagrid>
  </List>
);
