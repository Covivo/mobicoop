import React from 'react'
import { Link } from 'react-router-dom'
import { linkToRecord, Button} from 'react-admin'

const EditButtonWithBackLink = ({
    basePath = '',
    label = 'ra.action.edit',
    record,
    icon ,
    backTo,
    ...rest
}) => (
    <Button
        component={Link}
        to={{
            pathname: linkToRecord(basePath, record && record.id),
            backTo: backTo
        }}
        label={label}
        onClick={e => e.stopPropagation()}
        {...rest }
    >
        {icon}
    </Button>
)

export default EditButtonWithBackLink

