import React from 'react';
import { 
    Edit,
    SimpleForm,
    TextInput, ReferenceInput, SelectInput, ReferenceArrayInput, SelectArrayInput, 
    regex, required
} from 'react-admin';

const validateName = regex(/^ROLE_[A-Z_]+$/, 'Nom invalide');

export const RoleEdit = (props) => (
    <Edit {...props} title="Rôles > éditer">
        <SimpleForm>
            <TextInput disabled source="originId" label="ID"/>
            <TextInput source="title" label="Titre" validate={required()}/>
            <TextInput source="name" label="Nom" validate={[validateName,required()]} />
            <ReferenceInput source="parent" label="Rôle parent" reference="roles">
                <SelectInput optionText="title" />
            </ReferenceInput>
            <ReferenceArrayInput source="rights" label="Droits" reference="rights">
                <SelectArrayInput optionText="name" />
            </ReferenceArrayInput>
        </SimpleForm>
    </Edit>
);