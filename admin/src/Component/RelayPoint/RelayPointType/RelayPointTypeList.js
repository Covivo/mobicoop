import React from 'react';
import { 
    List,
    Datagrid,
    TextInput,
    TextField, Filter,
    ShowButton, EditButton
} from 'react-admin';

const RelayPointTypeFilter = (props) => (
    <Filter {...props}>
        <TextInput source="name" label="Nom" alwaysOn />
    </Filter>
);
export const RelayPointTypeList = (props) => (
    <List {...props} title="Types de points relais > liste" perPage={ 25 } filters={<RelayPointTypeFilter />} sort={{ field: 'originId', order: 'ASC' }}>
        <Datagrid>
            <TextField source="originId" label="ID" sortBy="id"/>
            <TextField source="name" label="Nom"/>
            <ShowButton />
            <EditButton />
        </Datagrid>
    </List>
);