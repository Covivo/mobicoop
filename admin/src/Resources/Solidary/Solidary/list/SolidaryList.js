import React from 'react';

import {
  List,
  Datagrid,
  TextField,
  ShowButton,
  FunctionField,
  DateField,
  Filter,
} from 'react-admin';

const TodoField = ({ detail }) => <span>@TODO: {detail}</span>;

const SolidaryFilter = (props) => <Filter {...props}>{/* TODO: See questions */}</Filter>;

export const SolidaryList = (props) => (
  <List
    {...props}
    bulkActionButtons={false}
    filters={<SolidaryFilter />}
    title="Demandes solidaires > liste"
    perPage={25}
  >
    <Datagrid>
      <TextField source="originId" label="ID" />
      <TextField source="subject.label" />
      <TodoField label="Trajet demandé" detail="Champ 'trajet'" />
      <TextField label="Prénom" source="solidaryUser.user.givenName" />
      <TextField label="Nom" source="solidaryUser.user.familyName" />
      <FunctionField label="% avanc." render={(r) => `${r.progression}%`} />
      <TodoField label="Dernière action" detail="Où trouver l'info?" />
      <DateField source="createdDate" label="Date" />
      <ShowButton />
    </Datagrid>
  </List>
);
