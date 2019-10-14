import React from 'react';
import { 
    Create,
    SimpleForm, required,
    TextInput, BooleanInput, ReferenceInput, SelectInput
} from 'react-admin';
import RichTextInput from 'ra-input-rich-text';
import GeocompleteInput from '../../Utilities/geocomplete';

const userOptionRenderer = choice => `${choice.givenName} ${choice.familyName}`;
const userId = `/users/${localStorage.getItem('id')}`;
const validationChoices = [
    { id: 0, name: 'Validation automatique' },
    { id: 1, name: 'Validation manuelle' },
    { id: 2, name: 'Validation par le domaine' },
];

export const CommunityCreate = (props) => (
    <Create { ...props } title="Communautés > ajouter">
        <SimpleForm>
            <ReferenceInput source="user" label="Créateur" reference="users" defaultValue={userId}>
                <SelectInput optionText={userOptionRenderer}/>
            </ReferenceInput>
            <GeocompleteInput source="address" label="Adresse" validate={required()}/>
            <TextInput source="name" label="Nom" validate={required()}/>
            <BooleanInput source="membersHidden" label="Membres masqués" />
            <BooleanInput source="proposalsHidden" label="Annonces masquées" />
            <SelectInput source="validationType" label="Type de validation"choices={validationChoices}/>
            <TextInput source="domain" label="Nom de domaine" />
            <TextInput source="description" label="Description" validate={required()}/>
            <RichTextInput source="fullDescription" label="Description complète" validate={required()}/>
            
        </SimpleForm>
    </Create>
);