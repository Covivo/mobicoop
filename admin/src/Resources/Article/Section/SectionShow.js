import React from 'react';

import {
  Show,
  TextField,
  SelectField,
  Link,
  Tab,
  TabbedShowLayout,
  Datagrid,
  Button,
  EditButton,
  RichTextField,
  DeleteButton,
  ReferenceArrayField,
} from 'react-admin';

import { ReferenceRecordIdMapper } from '../../../components/utils/ReferenceRecordIdMapper';

const statusChoices = [
  { id: 0, name: "En cours d'édition" },
  { id: 1, name: 'En ligne' },
];

const AddParagraphButton = ({ record }) => (
  <Button
    component={Link}
    to={{
      pathname: `/paragraphs/create`,
      state: { record: { section: record.id } },
    }}
    label="Ajouter un paragraphe"
  ></Button>
);
export const SectionShow = (props) => (
  <Show {...props} title="Articles > afficher une section">
    <TabbedShowLayout>
      <Tab label="Détails">
        <TextField source="originId" label="ID" />
        <TextField label="Article" source="article.title" />
        <SelectField source="status" label="Status" choices={statusChoices} />
        <TextField source="title" label="Titre" />
        <TextField source="subtitle" label="Sous-titre" />
        <TextField source="position" label="Position" />
        <EditButton />
      </Tab>
      <Tab label="Paragraphes" path="paragraphs">
        <ReferenceRecordIdMapper attribute="paragraphs">
          <ReferenceArrayField source="paragraphs" reference="paragraphs" addLabel={false}>
            <Datagrid>
              <RichTextField source="text" label="Texte" />
              <TextField source="position" label="Position" />
              <EditButton />
              <DeleteButton />
            </Datagrid>
          </ReferenceArrayField>
        </ReferenceRecordIdMapper>
        <AddParagraphButton />
      </Tab>
    </TabbedShowLayout>
  </Show>
);
