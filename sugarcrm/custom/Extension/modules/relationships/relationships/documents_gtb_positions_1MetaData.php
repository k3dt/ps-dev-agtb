<?php

$dictionary["documents_gtb_positions_1"] = array (
    'true_relationship_type' => 'many-to-many',
    'from_studio' => true,
    'relationships' =>
        array (
            'documents_gtb_positions_1' =>
                array (
                    'lhs_module' => 'Documents',
                    'lhs_table' => 'documents',
                    'lhs_key' => 'id',
                    'rhs_module' => 'gtb_positions',
                    'rhs_table' => 'gtb_positions',
                    'rhs_key' => 'id',
                    'relationship_type' => 'many-to-many',
                    'join_table' => 'documents_gtb_positions_1_c',
                    'join_key_lhs' => 'documents_gtb_positions_1documents_ida',
                    'join_key_rhs' => 'documents_gtb_positions_1gtb_positions_idb',
                ),
        ),
    'table' => 'documents_gtb_positions_1_c',
    'fields' =>
        array (
            'id' =>
                array (
                    'name' => 'id',
                    'type' => 'id',
                ),
            'date_modified' =>
                array (
                    'name' => 'date_modified',
                    'type' => 'datetime',
                ),
            'deleted' =>
                array (
                    'name' => 'deleted',
                    'type' => 'bool',
                    'default' => 0,
                ),
            'documents_gtb_positions_1documents_ida' =>
                array (
                    'name' => 'documents_gtb_positions_1documents_ida',
                    'type' => 'id',
                ),
            'documents_gtb_positions_1gtb_positions_idb' =>
                array (
                    'name' => 'documents_gtb_positions_1gtb_positions_idb',
                    'type' => 'id',
                ),
            'document_revision_id' =>
                array (
                    'name' => 'document_revision_id',
                    'type' => 'id',
                ),
        ),
    'indices' =>
        array (
            0 =>
                array (
                    'name' => 'idx_documents_gtb_positions_1_pk',
                    'type' => 'primary',
                    'fields' =>
                        array (
                            0 => 'id',
                        ),
                ),
            1 =>
                array (
                    'name' => 'idx_documents_gtb_positions_1_ida1_deleted',
                    'type' => 'index',
                    'fields' =>
                        array (
                            0 => 'documents_gtb_positions_1documents_ida',
                            1 => 'deleted',
                        ),
                ),
            2 =>
                array (
                    'name' => 'idx_documents_gtb_positions_1_idb2_deleted',
                    'type' => 'index',
                    'fields' =>
                        array (
                            0 => 'documents_gtb_positions_1gtb_positions_idb',
                            1 => 'deleted',
                        ),
                ),
            3 =>
                array (
                    'name' => 'documents_gtb_positions_1_alt',
                    'type' => 'alternate_key',
                    'fields' =>
                        array (
                            0 => 'documents_gtb_positions_1documents_ida',
                            1 => 'documents_gtb_positions_1gtb_positions_idb',
                        ),
                ),
        ),
);
