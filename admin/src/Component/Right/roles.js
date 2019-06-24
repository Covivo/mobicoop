import React from 'react';
import { 
    Create, Edit, List, Show,
    SimpleForm,
    SimpleShowLayout,
    Datagrid, 
    TextInput, ReferenceInput, SelectInput, ReferenceArrayInput, SelectArrayInput, DisabledInput, 
    regex, required,
    TextField, ReferenceField, ReferenceArrayField, SingleFieldList, ChipField,
    ShowButton, EditButton,
} from 'react-admin';

const validateName = regex(/^ROLE_[A-Z_]+$/, 'Nom invalide');

// List
export const RoleList = (props) => (
    <List {...props} title="Rôles > liste" perPage={ 25 }>
        <Datagrid>
            <TextField source="originId" label="ID" sortBy="id"/>
            <TextField source="title" label="Titre"/>
            <TextField source="name" label="Nom"/>
            <ReferenceField source="parent" label="Rôle parent" reference="roles" allowEmpty sortable={false}>
                <TextField source="title" />
            </ReferenceField>
            <ShowButton />
            <EditButton />
        </Datagrid>
    </List>
);

// Show
export const RoleShow = (props) => (
    <Show { ...props } title="Rôles > afficher">
        <SimpleShowLayout>
            <TextField source="originId" label="ID"/>
            <TextField source="title" label="Titre"/>
            <TextField source="name" label="Nom"/>
            <ReferenceField source="parent" label="Rôle parent" reference="rights" allowEmpty>
                <TextField source="name" />
            </ReferenceField>
            <ReferenceArrayField source="rights"label="Droits" reference="rights" >
                <SingleFieldList>
                    <ChipField source="name" />
                </SingleFieldList>
            </ReferenceArrayField>
            <EditButton />
        </SimpleShowLayout>
    </Show>
);

// Create
export const RoleCreate = (props) => (
    <Create { ...props } title="Rôles > ajouter">
        <SimpleForm>
            <TextInput source="title" label="Titre" validate={required()}/>
            <TextInput source="name" label="Nom" validate={[validateName,required()]} />
            <ReferenceInput source="parent_id" label="Rôle parent" reference="roles">
                <SelectInput optionText="title" />
            </ReferenceInput>
            <ReferenceArrayInput source="rights" label="Droits" reference="rights">
                <SelectArrayInput optionText="name" />
            </ReferenceArrayInput>
        </SimpleForm>
    </Create>
);

// Edit
export const RoleEdit = (props) => (
    <Edit {...props} title="Rôles > éditer">
        <SimpleForm>
            <DisabledInput source="originId" label="ID"/>
            <TextInput source="title" label="Titre" validate={required()}/>
            <TextInput source="name" label="Nom" validate={[validateName,required()]} />
            <ReferenceInput source="parent" label="Rôle parent" reference="roles">
                <SelectInput optionText="title" />
            </ReferenceInput>
            <ReferenceArrayInput source="rights" label="Droits" reference="rights">
                <SelectArrayInput optionText="name" />
            </ReferenceArrayInput>
        </SimpleForm>
    </Edit>
);