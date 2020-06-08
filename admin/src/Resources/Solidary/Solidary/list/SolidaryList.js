import React from 'react';

import {
  List,
  Datagrid,
  TextField,
  ShowButton,
  ReferenceField,
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
      <ReferenceField source="subject" reference="subjects" link={false}>
        <TextField source="label" />
      </ReferenceField>
      <TodoField label="Trajet demandé" detail="Champ 'trajet'" />
      <TodoField label="Prénom" detail="/!\ Accès deep" />
      <TodoField label="Nom" detail="/!\ Accès deep" />
      <TodoField label="% avanc." detail="/!\ Accès deep" />
      <TodoField label="Dernière action" detail="/!\ Accès deep" />
      <DateField source="createdDate" label="Date" />
      <ShowButton />
    </Datagrid>
  </List>
);
