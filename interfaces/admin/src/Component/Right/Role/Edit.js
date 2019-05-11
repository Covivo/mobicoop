import React from 'react';
import { Edit, SimpleForm, DisabledInput, TextInput, ReferenceInput, SelectInput, ReferenceArrayInput, SelectArrayInput, regex } from 'react-admin';

const validateName = regex(/^ROLE_[A-Z_]+$/, 'Nom invalide');

export const RoleEdit = (props) => (
    <Edit {...props}>
        <SimpleForm>
            <DisabledInput source="id" label="ID"/>
            <TextInput source="title" label="Titre"/>
            <TextInput source="name" label="Nom" validate={validateName} />
            <ReferenceInput label="Rôle parent" source="parent" reference="roles">
                <SelectInput optionText="title" />
            </ReferenceInput>
            <ReferenceArrayInput label="Droits" source="rights" reference="rights">
                <SelectArrayInput optionText="name" />
            </ReferenceArrayInput>
        </SimpleForm>
    </Edit>
);