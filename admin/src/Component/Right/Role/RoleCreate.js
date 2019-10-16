import React from 'react';
import { 
    Create,
    SimpleForm,
    TextInput, ReferenceInput, SelectInput, ReferenceArrayInput, SelectArrayInput,
    regex, required
} from 'react-admin';

const validateName = regex(/^ROLE_[A-Z_]+$/, 'Nom invalide');

export const RoleCreate = (props) => (
    <Create { ...props } title="Rôles > ajouter">
        <SimpleForm>
            <TextInput source="title" label="Titre" validate={required()}/>
            <TextInput source="name" label="Nom" validate={[validateName,required()]} />
            <ReferenceInput source="parent_id" label="Rôle parent" reference="roles">
                <SelectInput optionText="title" />
            </ReferenceInput>
            <ReferenceArrayInput source="rights" label="Droits" reference="rights">
                <SelectArrayInput optionText="name" />
            </ReferenceArrayInput>
        </SimpleForm>
    </Create>
);