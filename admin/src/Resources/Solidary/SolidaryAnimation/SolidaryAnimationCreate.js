import React from 'react';
import { Create, SimpleForm, ReferenceInput, NumberInput, SelectInput } from 'react-admin';

const ArticleCreate = (props) => {
  return (
    <Create {...props} title="Articles > ajouter">
      <SimpleForm>
        <NumberInput source="progression" label="Progression" min={0} max={100} />

        <ReferenceInput label="Demandeur solidaire" source="user" reference="users">
          <SelectInput optionText="email" />
        </ReferenceInput>

        <ReferenceInput label="Action" source="solidary" reference="solidaries">
          <SelectInput optionText="displayLabel" />
        </ReferenceInput>
      </SimpleForm>
    </Create>
  );
};

export default ArticleCreate;
