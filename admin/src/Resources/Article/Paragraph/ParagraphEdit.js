import React from 'react';
import RichTextInput from 'ra-input-rich-text';

import { Edit, SimpleForm, required, SelectInput, NumberInput, TextField } from 'react-admin';

const statusChoices = [
  { id: 0, name: "En cours d'édition" },
  { id: 1, name: 'En ligne' },
];

export const ParagraphEdit = (props) => {
  const redirect = `/sections/`;

  return (
    <Edit {...props} title="Articles > éditer un paragraphe">
      <SimpleForm redirect={redirect}>
        <TextField label="Section" source="section.title" />
        <SelectInput source="status" label="Statut" choices={statusChoices} />
        <RichTextInput source="text" label="Texte" validate={required()} />
        <NumberInput source="position" label="Position" />
      </SimpleForm>
    </Edit>
  );
};
