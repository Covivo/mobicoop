import React from 'react';
import {
    Edit,
    TextInput,
    TabbedForm,
    FormTab,
} from 'react-admin'
import { DateInput, TimeInput, DateTimeInput } from 'react-admin-date-inputs2';

export const DateInputPerso = (props) => (
                <DateInput source="startDate" label="Start date" options={{ format: 'DD/MM/YYYY' }} />

);