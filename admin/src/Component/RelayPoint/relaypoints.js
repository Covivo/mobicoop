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
                <ReferenceArrayInput label="Types" source="relayPointTypes" reference="relay_point_types">
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
                <ReferenceInput label="Créateur" source="user" reference="users" defaultValue={userId}>
                    <SelectInput optionText={userOptionRenderer}/>
                </ReferenceInput>
                <TextInput source="name" label="Nom" validate={required()}/>
                <SelectInput label="Status" source="status" choices={statusChoices} defaultValue={1} />
                <ReferenceArrayInput label="Types" source="relayPointTypes" reference="relay_point_types">
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

// List
const RelayPointFilter = (props) => (
    <Filter {...props}>
        <TextInput source="name" label="Nom" alwaysOn />
        <SelectInput label="Status" source="status" choices={statusChoices} />
    </Filter>
);
const RelayPointPanel = ({ id, record, resource }) => (
    <div dangerouslySetInnerHTML={{ __html: record.fullDescription }} />
);
export const RelayPointList = (props) => (
    <List {...props} title="Points relais > liste" perPage={ 25 } filters={<RelayPointFilter />}>
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
                <ReferenceField label="Créateur" source="user" reference="users" >
                    <FunctionField render={userOptionRenderer}/>
                </ReferenceField>
                <TextField source="name" label="Nom" />
                <SelectField label="Status" source="status" choices={statusChoices} />
                <ReferenceArrayField label="Types" source="relayPointTypes" reference="relay_point_types">
                    <SingleFieldList>
                        <ChipField source="name" />
                    </SingleFieldList>
                </ReferenceArrayField>
                <TextField source="description" label="Description" />
                <RichTextField source="fullDescription" label="Description complète" />
            </FormTab>
            <FormTab label="Adresse">
                <ReferenceField label="Rue" source="address" reference="addresses" linkType="">
                    <TextField source="streetAddress" />
                </ReferenceField>
                <ReferenceField label="Code postal" source="address" reference="addresses" linkType="">
                    <TextField source="postalCode" />
                </ReferenceField>
                <ReferenceField label="Ville" source="address" reference="addresses" linkType="">
                    <TextField source="addressLocality" />
                </ReferenceField>
                <ReferenceField label="Pays" source="address" reference="addresses" linkType="">
                    <TextField source="addressCountry" />
                </ReferenceField>
                <ReferenceField label="Latitude" source="address" reference="addresses" linkType="">
                    <TextField source="latitude" />
                </ReferenceField>
                <ReferenceField label="Longitude" source="address" reference="addresses" linkType="">
                    <TextField source="longitude" />
                </ReferenceField>
            </FormTab>
            <FormTab label="Communauté">
                <ReferenceField label="Communauté" source="community" reference="communities" allowEmpty>
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