import React from 'react';
import { Create, SimpleForm, TextInput, BooleanInput, ReferenceInput, SelectInput } from 'react-admin';
import RichTextInput from 'ra-input-rich-text';

const optionRenderer = choice => `${choice.givenName} ${choice.familyName}`;

export const CommunityCreate = (props) => (
    <Create { ...props }>
        <SimpleForm>
            <ReferenceInput label="Créateur" source="user" reference="users">
                <SelectInput optionText={optionRenderer} />
            </ReferenceInput>
            <TextInput source="name" label="Nom"/>
            <BooleanInput source="private" label="Privée" />
            <TextInput source="description" label="Description"/>
            <RichTextInput source="fullDescription" label="Description complète"/>
        </SimpleForm>
    </Create>
);