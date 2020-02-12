import React from 'react';


import { 
    Show,
    SimpleShowLayout, Labeled,
    TextField, ReferenceField,
    ImageField, DateField, FunctionField, UrlField
} from 'react-admin';

export const EventShow = (props) => (
    <Show { ...props } title="Evénement > afficher">
        <SimpleShowLayout>
            <ReferenceField reference="images" source="images[0]" addLabel={false}>
                <ImageField source="versions.square_100"/>
            </ReferenceField>
            <ReferenceField reference="users" source="user" addLabel={false}>
                <Labeled label="Créateur">
                    <FunctionField render={record => `${record.givenName} ${record.familyName}`} />
                </Labeled>
            </ReferenceField>
            <TextField source="name" label="Nom"/>
            <TextField component="pre" source="description" label="Description"/>
            <TextField component="pre" source="fullDescription" label="Description complète"/>
            <DateField source="fromDate" label="Date de début" showTime/>
            <DateField source="toDate" label="Date de fin" showTime/>
            <UrlField source="url" />
        </SimpleShowLayout>
    </Show>
);