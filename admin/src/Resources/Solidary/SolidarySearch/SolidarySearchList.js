import React from 'react';

import { List, Datagrid, TextField } from 'react-admin';

const SolidarySearchList = (props) => (
  <List {...props} title="Liste des covoiturages" perPage={25}>
    <Datagrid>
      <TextField label="Auteur" source="author" />
      <TextField label="Origine" source="origin" />
      <TextField label="Destination" source="destination" />
    </Datagrid>
  </List>
);

export default SolidarySearchList;
