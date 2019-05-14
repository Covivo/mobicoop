import React from 'react';
import { 
    Create, Edit, List, Show,
    SimpleForm, 
    SimpleShowLayout,
    Datagrid, required, number, 
    TextInput, DisabledInput, BooleanInput, ReferenceInput, SelectInput, ReferenceArrayInput, SelectArrayInput,
    TextField, DateField, 
    ShowButton, EditButton,
} from 'react-admin';
import RichTextInput from 'ra-input-rich-text';

const userOptionRenderer = choice => `${choice.givenName} ${choice.familyName}`;
const userId = `/users/${localStorage.getItem('id')}`;
const statusChoices = [
    { id: 0, name: 'En attente' },
    { id: 1, name: 'Actif' },
    { id: 2, name: 'Inactif' },
];

// Create
export const RelayPointCreate = (props) => (
    <Create { ...props } title="Points relais > ajouter">
        <SimpleForm>
            <ReferenceInput label="Créateur" source="user" reference="users" defaultValue={userId}>
                <SelectInput optionText={userOptionRenderer}/>
            </ReferenceInput>
            <TextInput source="name" label="Nom" validate={required()}/>
            <SelectInput label="Status" source="status" choices={statusChoices} defaultValue={1} />
            <ReferenceArrayInput label="Types" source="relay_point_types" reference="relay_point_types">
                <SelectArrayInput optionText="name" />
            </ReferenceArrayInput>
            <BooleanInput source="private" label="Privé" />
            <TextInput source="description" label="Description" validate={required()}/>
            <RichTextInput source="fullDescription" label="Description complète" validate={required()}/>
            <TextInput source="places" label="Nombre de places" validate={number()}/>
            <TextInput source="placesDisabled" label="Nombre de places handicapés" validate={number()}/>
            <BooleanInput source="free" label="Gratuit" defaultValue={1} />
            <BooleanInput source="secured" label="Sécurisé" />
            <BooleanInput source="official" label="Officiel" />
            <BooleanInput source="suggested" label="Suggestion autocomplétion" />
        </SimpleForm>
    </Create>
);

// Edit
export const RelayPointEdit = (props) => (
    <Edit {...props} title="Points relais > éditer">
        <SimpleForm>
            <DisabledInput source="originId" label="ID"/>
            <ReferenceInput label="Créateur" source="user" reference="users" defaultValue={userId}>
                <SelectInput optionText={userOptionRenderer}/>
            </ReferenceInput>
            <TextInput source="name" label="Nom" validate={required()}/>
            <SelectInput label="Status" source="status" choices={statusChoices} defaultValue={1} />
            <ReferenceArrayInput label="Types" source="relay_point_types" reference="relay_point_types">
                <SelectArrayInput optionText="name" />
            </ReferenceArrayInput>
            <BooleanInput source="private" label="Privé" />
            <TextInput source="description" label="Description" validate={required()}/>
            <RichTextInput source="fullDescription" label="Description complète" validate={required()}/>
            <TextInput source="places" label="Nombre de places" validate={number()}/>
            <TextInput source="placesDisabled" label="Nombre de places handicapés" validate={number()}/>
            <BooleanInput source="free" label="Gratuit" defaultValue={1} />
            <BooleanInput source="secured" label="Sécurisé" />
            <BooleanInput source="official" label="Official" />
            <BooleanInput source="suggested" label="Suggestion autocomplétion" />
        </SimpleForm>
    </Edit>
);

// List
export const RelayPointList = (props) => (
    <List {...props} title="Points relais > liste" perPage={ 30 }>
        <Datagrid>
            <TextField source="originId" label="ID"/>
            <TextField source="name" label="Nom"/>
            <DateField source="createdDate" label="Date de création"/>
            <ShowButton />
            <EditButton />
        </Datagrid>
    </List>
);

// Show
export const RelayPointShow = (props) => (
    <Show { ...props } title="Points relais > afficher">
        <SimpleShowLayout>
            <TextField source="originId" label="ID"/>
            <TextField source="name" label="Nom"/>
            <DateField source="createdDate" label="Date de création"/>
            <EditButton />
        </SimpleShowLayout>
    </Show>
);