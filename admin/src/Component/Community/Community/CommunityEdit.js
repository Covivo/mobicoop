import React from 'react';
import { 
    Edit,
    SimpleForm, required,
    DisabledInput, TextInput, DateInput, BooleanInput, ReferenceInput, SelectInput,
    FunctionField, ReferenceField
} from 'react-admin';
import RichTextInput from 'ra-input-rich-text';
import GeocompleteInput from '../../Utilities/geocomplete';

const userOptionRenderer = choice => `${choice.givenName} ${choice.familyName}`;
const addressRenderer = address => `${address.displayLabel[0]} - ${address.displayLabel[1]}`;
const validationChoices = [
    { id: 0, name: 'Validation automatique' },
    { id: 1, name: 'Validation manuelle' },
    { id: 2, name: 'Validation par le domaine' },
];

export const CommunityEdit = (props) => (
    <Edit {...props } title="Communautés > éditer">
        <SimpleForm>
            <DisabledInput source="originId" label="ID"/>
            <ReferenceInput source="user" label="Créateur" reference="users">
                <SelectInput optionText={userOptionRenderer} />
            </ReferenceInput>
            <ReferenceField source="address" label="Adresse actuelle" reference="addresses" linkType="">
                <FunctionField render={addressRenderer} />
            </ReferenceField>
            <GeocompleteInput source="address" label="Nouvelle addresse" validate={required()}/>
            <TextInput source="name" label="Nom" validate={required()}/>
            <BooleanInput source="membersHidden" label="Membres masqués"  />
            <BooleanInput source="proposalsHidden" label="Annonces masquées" />
            <SelectInput source="validationType" label="Type de validation"choices={validationChoices}/>
            <TextInput source="domain" label="Nom de domaine" />
            <TextInput source="description" label="Description" validate={required()}/>
            <RichTextInput source="fullDescription" label="Description complète" validate={required()} />
            <DateInput disabled source="createdDate" label="Date de création"/>
        </SimpleForm>
    </Edit>
);