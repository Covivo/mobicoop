import React from 'react';
import { 
    List,
    Datagrid,
    EditButton,DateField,NumberField,EmailField,ReferenceField,
    TextField, ReferenceManyField, ChipField, SingleFieldList, SelectField,ListGuesser,FieldGuesser
} from 'react-admin';

const statusChoices = [
    { id: 0, name: 'En cours d\'édition' },
    { id: 1, name: 'En ligne' },
];

export const CampaignsList = props => (
    <List {...props}>
        <Datagrid rowClick="edit">
            <TextField source="subject" />
            <EmailField source="email" />
            <TextField source="fromName" />
            <TextField source="replyTo" />
            <TextField source="body" />
            <TextField source="status" />
            <DateField source="medium" />
            <DateField source="createdDate" />
            <DateField source="updatedDate" />
        </Datagrid>
    </List>
);