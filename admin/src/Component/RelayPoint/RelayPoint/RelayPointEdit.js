import React from 'react';
import { 
    Edit,
    TabbedForm, FormTab, 
    required, 
    TextInput, BooleanInput, ReferenceInput, SelectInput, ReferenceArrayInput, SelectArrayInput, NumberInput, 
    FunctionField, ReferenceField,
    FormDataConsumer
} from 'react-admin';
import RichTextInput from 'ra-input-rich-text';
import GeocompleteInput from '../../Utilities/geocomplete';

const userOptionRenderer = choice => `${choice.givenName} ${choice.familyName}`;
const addressRenderer = address => `${address.displayLabel[0]} - ${address.displayLabel[1]}`;
const userId = `/users/${localStorage.getItem('id')}`;
const statusChoices = [
    { id: 0, name: 'En attente' },
    { id: 1, name: 'Actif' },
    { id: 2, name: 'Inactif' },
];  

export const RelayPointEdit = (props) => (
    <Edit {...props} title="Points relais > éditer">
        <TabbedForm>
            <FormTab label="Identité">
                <ReferenceInput source="user" label="Créateur" reference="users" defaultValue={userId}>
                    <SelectInput optionText={userOptionRenderer}/>
                </ReferenceInput>
                <TextInput source="name" label="Nom" validate={required()}/>
                <ReferenceField source="address" label="Adresse actuelle" reference="addresses" linkType="">
                    <FunctionField render={addressRenderer} />
                </ReferenceField>
                <GeocompleteInput source="address" label="Nouvelle addresse" validate={required()}/>
                <SelectInput source="status" label="Status" choices={statusChoices} defaultValue={1} />
                <ReferenceArrayInput source="relayPointTypes" label="Types" reference="relay_point_types">
                    <SelectArrayInput optionText="name" />
                </ReferenceArrayInput>
                <TextInput source="description" label="Description" validate={required()}/>
                <RichTextInput source="fullDescription" label="Description complète" validate={required()}/>
            </FormTab>
            <FormTab label="Communauté">
                <ReferenceInput source="community" label="Communauté" reference="communities" resettable>
                    <SelectInput optionText="name" />
                </ReferenceInput>
                <FormDataConsumer>
                    {({ formData, ...rest }) => formData.community &&
                        <BooleanInput source="private" label="Privé à cette communauté" {...rest}/>
                    }
                </FormDataConsumer>
            </FormTab>
            <FormTab label="Propriétés">
                <NumberInput source="places" label="Nombre de places" />
                <NumberInput source="placesDisabled" label="Nombre de places handicapés" />
                <BooleanInput source="free" label="Gratuit" defaultValue={true} />
                <BooleanInput source="secured" label="Sécurisé" />
                <BooleanInput source="official" label="Officiel" />
                <BooleanInput source="suggested" label="Suggestion autocomplétion" />
            </FormTab>
            {/* <FormTab label="Images">
                <ImageInput source="images" label="Images" accept="image/*">
                    <ImageField source="src" title="title" />
                </ImageInput>
            </FormTab> */}
        </TabbedForm>
    </Edit>
);