import React from 'react';
import { 
    Create, Edit,
    SimpleForm, 
    required,
    ReferenceInput, SelectInput,
    ReferenceField, FunctionField, TextField
} from 'react-admin';
import { parse } from "query-string";

const userOptionRenderer = choice => `${choice.givenName} ${choice.familyName}`;
const userId = `/users/${localStorage.getItem('id')}`;

const choices = [
    { id: 0, name: 'En attente' },
    { id: 1, name: 'Accepté' },
    { id: 2, name: 'Refusé' },
];

// Create
export const CommunityUserCreate = (props) => {
    const { community: community_string } = parse(props.location.search);
    const community = community_string ? parseInt(community_string, 10) : '';
    const community_uri = encodeURIComponent(community_string);
    const redirect = community_uri ? `/communities/${community_uri}/show/members` : 'show';

    return (
    <Create { ...props } title="Communautés > ajouter un membre">
        <SimpleForm
            defaultValue={{ community }}
            redirect={redirect}
        >
            <ReferenceInput label="Administrateur" source="admin" reference="users" defaultValue={userId} validate={required()}>
                <SelectInput optionText={userOptionRenderer}/>
            </ReferenceInput>
            <ReferenceInput label="Membre" source="user" reference="users" validate={required()}>
                <SelectInput optionText={userOptionRenderer}/>
            </ReferenceInput>
            <ReferenceInput label="Communauté" source="community" reference="communities" validate={required()}>
                <SelectInput optionText="name"/>
            </ReferenceInput>
            <SelectInput label="Status" source="status" choices={choices} defaultValue={1} validate={required()}/>
        </SimpleForm>
    </Create>
    );
}

// Edit
export const CommunityUserEdit = (props) => {
    
    const redirect = `/communities/`;

    return (
    <Edit { ...props } title="Communautés > éditer un membre">
        <SimpleForm
            redirect={redirect}
        >
            <ReferenceInput label="Administrateur" source="admin" reference="users">
                <SelectInput optionText={userOptionRenderer}/>
            </ReferenceInput>
            <ReferenceField label="Membre" source="user" reference="users" linkType="" >
                <FunctionField render={userOptionRenderer} />
            </ReferenceField>
            <ReferenceField label="Communauté" source="community" reference="communities" linkType="" >
                <TextField source="name"/>
            </ReferenceField>
            <SelectInput label="Status" source="status" choices={choices} />
        </SimpleForm>
    </Edit>
    );
}
