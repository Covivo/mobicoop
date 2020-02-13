import React from 'react';
import { 
    Edit,
    SimpleForm, 
    ReferenceInput, SelectInput,
    ReferenceField, FunctionField, TextField
} from 'react-admin';

const userOptionRenderer = choice => `${choice.givenName} ${choice.familyName}`;

const choices = [
    { id: 0, name: 'En attente' },
    { id: 1, name: 'Membre' },
    { id: 2, name: 'Modérateur' },
    { id: 3, name: 'Refusé' },
];
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
            <SelectInput label="Statut" source="status" choices={choices} />
        </SimpleForm>
    </Edit>
    );
}