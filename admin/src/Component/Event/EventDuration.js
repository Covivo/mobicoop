import React from 'react'
import { FormDataConsumer, DateTimeInput, DateInput, required,useTranslate} from 'react-admin'

const EventDuration = (props) => {
const translate = useTranslate();
    return (
        <FormDataConsumer >
            {({formData}) => {
                return formData.useTime ? (
                    <>
                        <DateTimeInput source="fromDate"  label={translate('custom.label.event.dateTimeStart')} validate={[required()]} style={{marginRight: '1rem'}} />
                        <DateTimeInput source="toDate" label={translate('custom.label.event.dateTimeFin')} validate={[required()]} />
                    </>
                ) : (
                    <>
                        <DateInput source="fromDate"  label={translate('custom.label.event.dateStart')} validate={[required()]} style={{marginRight: '1rem'}}/>
                        <DateInput source="toDate"  label={translate('custom.label.event.dateFin')} validate={[required()]} />
                    </>
                )
                }
            }
        </FormDataConsumer>
    )
}

export default EventDuration

