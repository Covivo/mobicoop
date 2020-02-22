import React from 'react';
import {isAuthorized} from '../Utilities/authorization';

import { 
    List,
    Datagrid,
    TextField, DateField, ImageField,
    ReferenceField, EditButton
} from 'react-admin';
const EventPanel = ({ id, record, resource }) => (
    <div dangerouslySetInnerHTML={{ __html: record.fullDescription }} />
);



export const EventList = (props) => (
    <List {...props} title="Evénement > liste" perPage={ 25 } >
        <Datagrid expand={<EventPanel />} rowClick="show">
            <ReferenceField reference="images" source="images[0]" label="Image">
                <ImageField source="versions.square_100"/>
            </ReferenceField>
            <TextField source="originId" label="ID" sortBy="id"/>
            <TextField source="name" label="Nom"/>
            <DateField source="fromDate" label="Début" showTime/>
            <DateField source="toDate" label="Fin" showTime/>
            {isAuthorized("event_update") && 
                <EditButton />
            }
        </Datagrid>
    </List>
);