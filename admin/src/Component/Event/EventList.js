import React from 'react';
import isAuthorized from '../../Auth/permissions'
import Paper from '@material-ui/core/Paper'

import { 
    List,
    Datagrid,
    TextField, DateField, ImageField,
    ReferenceField, EditButton,useTranslate
} from 'react-admin';

const EventPanel = ({ id, record, resource }) => (
    <Paper style={{padding:'1rem'}}>
        <div dangerouslySetInnerHTML={{ __html: record.fullDescription }} />
        { record.url && <p><a href={record.url}>{record.url}</a></p>}
    </Paper> 

);

export const EventList = (props) => {
    const translate = useTranslate();
    return (
        <List {...props} title="Evénement > liste" perPage={25}>
            <Datagrid expand={<EventPanel/>} rowClick="show">
                <ReferenceField reference="images" source="images[0]" label={translate('custom.label.event.image')}>
                    <ImageField source="versions.square_100"/>
                </ReferenceField>
                <TextField source="name" label={translate('custom.label.event.name')}/>
                <DateField source="fromDate" label={translate('custom.label.event.dateStart')}/>
                <DateField source="toDate" label={translate('custom.label.event.dateFin')}/>
                {isAuthorized("event_update") &&
                <EditButton/>
                }
            </Datagrid>
        </List>
    )
};
