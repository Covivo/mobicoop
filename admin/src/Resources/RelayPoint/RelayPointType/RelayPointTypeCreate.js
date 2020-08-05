import React from 'react';
import { Create, SimpleForm, required, TextInput, ReferenceInput, SelectInput } from 'react-admin';

const RelayPointTypeCreate = (props) => {
  return (
    <Create {...props} title="Types de points relais > ajouter">
      <SimpleForm>
        <TextInput source="name" label="Nom" validate={required()} />
        <ReferenceInput source="icon" label="Icones" reference="icons" allowEmpty>
          <SelectInput source="name" />
        </ReferenceInput>
      </SimpleForm>
    </Create>
  );
};

export default RelayPointTypeCreate;
