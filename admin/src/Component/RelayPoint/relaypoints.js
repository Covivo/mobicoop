import React from 'react';
import { 
    Create, Edit, List, Show,
    TabbedForm, FormTab, 
    TabbedShowLayout,
    Datagrid, required, 
    TextInput, BooleanInput, ReferenceInput, SelectInput, ReferenceArrayInput, SelectArrayInput, NumberInput, 
    TextField, BooleanField, ReferenceField, SelectField, ReferenceArrayField, SingleFieldList, ChipField, NumberField, RichTextField, FunctionField,
    //ImageInput, ImageField,
    Filter,
    FormDataConsumer,
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

// List
const RelayPointFilter = (props) => (
    <Filter {...props}>
        <TextInput source="name" label="Nom" alwaysOn />
        <SelectInput source="status" label="Status" choices={statusChoices} />
    </Filter>
);
const RelayPointPanel = ({ id, record, resource }) => (
    <div dangerouslySetInnerHTML={{ __html: record.fullDescription }} />
);
export const RelayPointList = (props) => (
    <List {...props} title="Points relais > liste" perPage={ 25 } filters={<RelayPointFilter />} sort={{ field: 'originId', order: 'ASC' }}>
        <Datagrid expand={<RelayPointPanel />}>
            <TextField source="originId" label="ID" sortBy="id"/>
            <TextField source="name" label="Nom"/>
            <SelectField source="status" label="Status" choices={statusChoices} sortable={false} />
            <TextField source="description" label="Description"/>
            <ShowButton />
            <EditButton />
        </Datagrid>
    </List>
);

// Show
export const RelayPointShow = (props) => (
    <Show { ...props } title="Points relais > afficher">
        <TabbedShowLayout>
            <FormTab label="Identité">
                <ReferenceField source="user" label="Créateur" reference="users" >
                    <FunctionField render={userOptionRenderer}/>
                </ReferenceField>
                <TextField source="name" label="Nom" />
                <SelectField source="status" label="Status" choices={statusChoices} />
                <ReferenceArrayField source="relayPointTypes" label="Types" reference="relay_point_types">
                    <SingleFieldList>
                        <ChipField source="name" />
                    </SingleFieldList>
                </ReferenceArrayField>
                <TextField source="description" label="Description" />
                <RichTextField source="fullDescription" label="Description complète" />
            </FormTab>
            <FormTab label="Adresse">
                <ReferenceField source="address" label="Rue" reference="addresses" linkType="">
                    <TextField source="streetAddress" />
                </ReferenceField>
                <ReferenceField source="address" label="Code postal" reference="addresses" linkType="">
                    <TextField source="postalCode" />
                </ReferenceField>
                <ReferenceField source="address" label="Ville" reference="addresses" linkType="">
                    <TextField source="addressLocality" />
                </ReferenceField>
                <ReferenceField source="address" label="Pays" reference="addresses" linkType="">
                    <TextField source="addressCountry" />
                </ReferenceField>
                <ReferenceField source="address" label="Latitude" reference="addresses" linkType="">
                    <TextField source="latitude" />
                </ReferenceField>
                <ReferenceField source="address" label="Longitude" reference="addresses" linkType="">
                    <TextField source="longitude" />
                </ReferenceField>
            </FormTab>
            <FormTab label="Communauté">
                <ReferenceField source="community" label="Communauté" reference="communities" allowEmpty>
                    <TextField source="name" />
                </ReferenceField>
                <BooleanField source="private" label="Privé à cette communauté" />
            </FormTab>
            <FormTab label="Propriétés">
                <NumberField source="places" label="Nombre de places" />
                <NumberField source="placesDisabled" label="Nombre de places handicapés" />
                <BooleanField source="free" label="Gratuit" />
                <BooleanField source="secured" label="Sécurisé" />
                <BooleanField source="official" label="Officiel" />
                <BooleanField source="suggested" label="Suggestion autocomplétion" />
            </FormTab>
        </TabbedShowLayout>
    </Show>
);

// Create
export const RelayPointCreate = (props) => (
    <Create { ...props } title="Points relais > ajouter">
        <TabbedForm>
            <FormTab label="Identité">
                <ReferenceInput source="user" label="Créateur" reference="users" defaultValue={userId}>
                    <SelectInput optionText={userOptionRenderer}/>
                </ReferenceInput>
                <TextInput source="name" label="Nom" validate={required()}/>
                <SelectInput source="status" label="Status" choices={statusChoices} defaultValue={1} />
                <ReferenceArrayInput source="relayPointTypes" label="Types" reference="relay_point_types">
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
                <NumberInput source="places" label="Nombre de places"/>
                <NumberInput source="placesDisabled" label="Nombre de places handicapés"/>
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
                <ReferenceInput source="user" label="Créateur" reference="users" defaultValue={userId}>
                    <SelectInput optionText={userOptionRenderer}/>
                </ReferenceInput>
                <TextInput source="name" label="Nom" validate={required()}/>
                <SelectInput source="status" label="Status" choices={statusChoices} defaultValue={1} />
                <ReferenceArrayInput source="relayPointTypes" label="Types" reference="relay_point_types">
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