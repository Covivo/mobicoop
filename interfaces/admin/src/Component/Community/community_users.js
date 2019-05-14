import React from 'react';
import { Create, SimpleForm, ReferenceInput, SelectInput } from 'react-admin';

const optionRenderer = choice => `${choice.givenName} ${choice.familyName}`;

const choices = [
    { id: 0, name: 'En attente' },
    { id: 1, name: 'Accepté' },
    { id: 2, name: 'Refusé' },
];

export const CommunityUserCreate = (props) => (
    <Create { ...props }>
        <SimpleForm
        >
            <ReferenceInput label="Membre" source="user" reference="users">
                <SelectInput optionText={optionRenderer}/>
            </ReferenceInput>
            <ReferenceInput label="Communauté" source="community" reference="communities">
                <SelectInput optionText="name"/>
            </ReferenceInput>
            <SelectInput label="Status" source="status" choices={choices} defaultValue={1}/>
        </SimpleForm>
    </Create>
);