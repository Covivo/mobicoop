import React from 'react';
import { 
    Create, Edit, List, Show,
    SimpleForm, TabbedForm, FormTab, 
    SimpleShowLayout,
    Datagrid, required, number, 
    TextInput, DisabledInput, BooleanInput, ReferenceInput, SelectInput, ReferenceArrayInput, SelectArrayInput, NumberInput, 
    //ImageInput, ImageField,
    FormDataConsumer,
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
        <TabbedForm>
            <FormTab label="Identité">
                <ReferenceInput label="Créateur" source="user" reference="users" defaultValue={userId}>
                    <SelectInput optionText={userOptionRenderer}/>
                </ReferenceInput>
                <TextInput source="name" label="Nom" validate={required()}/>
                <SelectInput label="Status" source="status" choices={statusChoices} defaultValue={1} />
                <ReferenceArrayInput label="Types" source="relay_point_types" reference="relay_point_types">
                    <SelectArrayInput optionText="name" />
                </ReferenceArrayInput>
                <TextInput source="description" label="Description" validate={required()}/>
                <RichTextInput source="fullDescription" label="Description complète" validate={required()}/>
            </FormTab>
            <FormTab label="Adresse">
                <TextInput source="address.streetAddress" label="Rue" />
                <TextInput source="address.postalCode" label="Code postal" />
                <TextInput source="address.addressLocality" label="Ville" />
                <TextInput source="address.addressCountry" label="Pays" />
                <NumberInput source="address.latitude" label="Latitude" parse={ v => v.toString() } />
                <NumberInput source="address.longitude" label="Longitude" parse={ v => v.toString() } />
            </FormTab>
            <FormTab label="Communauté">
                <ReferenceInput label="Communauté" source="community" reference="communities" resettable>
                    <SelectInput optionText="name" />
                </ReferenceInput>
                <FormDataConsumer>
                    {({ formData, ...rest }) => formData.community &&
                        <BooleanInput source="private" label="Privé à cette communauté" {...rest}/>
                    }
                </FormDataConsumer>
            </FormTab>
            <FormTab label="Propriétés">
                <TextInput source="places" label="Nombre de places" validate={number()}/>
                <TextInput source="placesDisabled" label="Nombre de places handicapés" validate={number()}/>
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
    </Create>
);

// Edit
export const RelayPointEdit = (props) => (
    <Edit {...props} title="Points relais > éditer">
        <TabbedForm>
            <FormTab label="Identité">
                <ReferenceInput label="Créateur" source="user" reference="users" defaultValue={userId}>
                    <SelectInput optionText={userOptionRenderer}/>
                </ReferenceInput>
                <TextInput source="name" label="Nom" validate={required()}/>
                <SelectInput label="Status" source="status" choices={statusChoices} defaultValue={1} />
                <ReferenceArrayInput label="Types" source="relay_point_types" reference="relay_point_types">
                    <SelectArrayInput optionText="name" />
                </ReferenceArrayInput>
                <TextInput source="description" label="Description" validate={required()}/>
                <RichTextInput source="fullDescription" label="Description complète" validate={required()}/>
            </FormTab>
            <FormTab label="Adresse">
                
            </FormTab>
            <FormTab label="Communauté">
                <ReferenceInput label="Communauté" source="community" reference="communities" resettable>
                    <SelectInput optionText="name" />
                </ReferenceInput>
                <FormDataConsumer>
                    {({ formData, ...rest }) => formData.community &&
                        <BooleanInput source="private" label="Privé à cette communauté" {...rest}/>
                    }
                </FormDataConsumer>
            </FormTab>
            <FormTab label="Propriétés">
                <TextInput source="places" label="Nombre de places" validate={number()}/>
                <TextInput source="placesDisabled" label="Nombre de places handicapés" validate={number()}/>
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