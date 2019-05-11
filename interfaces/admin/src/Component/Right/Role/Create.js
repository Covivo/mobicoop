import React from 'react';
import { Create, SimpleForm, TextInput, ReferenceInput, SelectInput, ReferenceArrayInput, SelectArrayInput, regex } from 'react-admin';

const validateName = regex(/^ROLE_[A-Z_]*$/, 'Nom invalide');

export const RoleCreate = (props) => (
    <Create { ...props }>
        <SimpleForm>
            <TextInput source="title" label="Titre"/>
            <TextInput source="name" label="Nom" validate={validateName} />
            <ReferenceInput label="Rôle parent" source="parent_id" reference="roles">
                <SelectInput optionText="title" />
            </ReferenceInput>
            <ReferenceArrayInput label="Droits" source="rights" reference="rights">
                <SelectArrayInput optionText="name" />
            </ReferenceArrayInput>
        </SimpleForm>
    </Create>
);