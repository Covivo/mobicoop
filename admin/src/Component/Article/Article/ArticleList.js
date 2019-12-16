import React from 'react';
import { 
    List,
    Datagrid,
    ShowButton, EditButton,
    TextField, ReferenceManyField, ChipField, SingleFieldList, SelectField
} from 'react-admin';

const statusChoices = [
    { id: 0, name: 'En cours d\'édition' },
    { id: 1, name: 'En ligne' },
];

export const ArticleList = (props) => (
    <List {...props} title="Articles > liste" perPage={ 25 } sort={{ field: 'originId', order: 'ASC' }}>
        <Datagrid rowClick="show">
            <TextField source="originId" label="ID" sortBy="id"/>
            <TextField source="title" label="Titre"/>
            <SelectField source="status" label="Status" choices={statusChoices} />
            <ReferenceManyField label="Sections" reference="sections" target="article" sortable={false}>
                <SingleFieldList linkType="show">
                    <ChipField source="title" />
                </SingleFieldList>
            </ReferenceManyField>
            <EditButton />
        </Datagrid>
    </List>
);